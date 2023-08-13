<?php
declare(strict_types=1);

namespace HuraTabory\API\V1\Authentication;


use Google\Client;
use Google_Client;
use Hafo\DI\Container;
use Hafo\Security\Storage\Avatars;
use HuraTabory\API\V1\Authentication\Service\JwtService;
use HuraTabory\API\V1\Authentication\Service\TokenUser;
use HuraTabory\API\V1\Authentication\Transformer\UserToAuthenticationArrayTransformer;
use HuraTabory\DataProvider\Notification\NotificationsDataProvider;
use HuraTabory\Http\HeadersFactory;
use Nette\Utils\Image;
use Nette\Utils\Random;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;
use VCD2\Orm;
use VCD2\Users\Service\UserSessions;
use VCD2\Users\User;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\JsonResponse;

class GoogleLoginAction implements RequestHandlerInterface
{
    /** @var Client */
    private $googleClient;

    /** @var Orm */
    private $orm;

    /** @var UserSessions */
    private $userSessions;

    /** @var JwtService */
    private $jwtService;

    /** @var TokenUser */
    private $tokenUser;

    /** @var UserToAuthenticationArrayTransformer */
    private $userToAuthenticationArrayTransformer;

    /** @var Avatars */
    private $avatars;

    /** @var HeadersFactory */
    private $headersFactory;

    /** @var NotificationsDataProvider */
    private $notificationsDataProvider;

    public function __construct(
        Client $googleClient,
        Orm $orm,
        UserSessions $userSessions,
        JwtService $jwtService,
        TokenUser $tokenUser,
        UserToAuthenticationArrayTransformer $userToAuthenticationArrayTransformer,
        Avatars $avatars,
        HeadersFactory $headersFactory,
        NotificationsDataProvider $notificationsDataProvider
    ) {
        $this->googleClient = $googleClient;
        $this->orm = $orm;
        $this->userSessions = $userSessions;
        $this->jwtService = $jwtService;
        $this->tokenUser = $tokenUser;
        $this->userToAuthenticationArrayTransformer = $userToAuthenticationArrayTransformer;
        $this->avatars = $avatars;
        $this->headersFactory = $headersFactory;
        $this->notificationsDataProvider = $notificationsDataProvider;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $headers = $this->headersFactory->createDefault();

        $body = (array)json_decode((string)$request->getBody(), true);

        if (!isset($body['token'])) {
            return new EmptyResponse(400, $headers->toArray());
        }

        $token = (string)$body['token'];

        $payload = $this->googleClient->verifyIdToken($token);

        if (!$payload) {
            return new EmptyResponse(401, $headers->toArray());
        }

        $user = $this->orm->users->getBy(['googleId' => $payload['sub']]);

        if ($user === null) {
            $user = $this->orm->users->getByEmail($payload['email']);
            if ($user !== null) {
                $user->googleId = $payload['sub'];
                $user->googleEmail = $payload['email'];
                $user->googleName = $payload['name'];
                $this->orm->persistAndFlush($user);
            }
        }

        if ($user === null) {
            $user = new User($payload['email'], $payload['name']);
            $user->googleId = $payload['sub'];
            $user->googleEmail = $payload['email'];
            $user->googleName = $payload['name'];
            $user->emailVerified = $payload['email_verified'] === 'true';
            $user->loginHash = Random::generate(40);

            $this->orm->persistAndFlush($user);
        }

        if (isset($payload['picture'])) {
            try {
                $isFromGoogle = $this->avatars->isFromSource($user->id, [Avatars::SOURCE_UNKNOWN, Avatars::SOURCE_GOOGLE]);
                $isOld = $this->avatars->isOlderThan($user->id, '-1 month');

                if ($isFromGoogle && $isOld) {
                    $imageData = file_get_contents($payload['picture']);
                    if ($imageData) {
                        $avatar = Image::fromString((string)$imageData);
                        $this->avatars->setAvatar($user->id, $avatar, Avatars::SOURCE_GOOGLE);

                        $user = $this->orm->users->get($user->id); // reload with avatar
                    }
                }
            } catch (Throwable $e) {
                // nevermind
            }
        }

        $session = $this->userSessions->createSession($user, $request);

        $jwt = $this->jwtService->buildJwt($user->id, $session->id);
        $refreshToken = $this->jwtService->buildRefreshToken($user->id, $session->id);

        $this->tokenUser->setUserSession($session);

        return new JsonResponse([
            'authentication' => $this->userToAuthenticationArrayTransformer->transform($session, $jwt),
            'notifications' => $this->notificationsDataProvider->getData($user),

        ], 200, $headers->withCookie($this->headersFactory->getRefreshTokenCookieTemplate(), $refreshToken)->toArray());
    }
}
