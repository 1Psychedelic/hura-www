<?php
declare(strict_types=1);

namespace HuraTabory\API\V1\Authentication;


use Google\Client;
use Hafo\Security\Storage\Avatars;
use HuraTabory\API\V1\Authentication\Exception\InvalidTokenException;
use HuraTabory\API\V1\Authentication\Service\TokenUser;
use HuraTabory\API\V1\Authentication\Transformer\UserToAuthenticationArrayTransformer;
use HuraTabory\Http\HeadersFactory;
use Nette\Utils\Image;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;
use VCD2\Orm;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\JsonResponse;

class GoogleConnectAction implements RequestHandlerInterface
{
    /** @var Client */
    private $googleClient;

    /** @var Orm */
    private $orm;

    /** @var TokenUser */
    private $tokenUser;

    /** @var UserToAuthenticationArrayTransformer */
    private $userToAuthenticationArrayTransformer;

    /** @var Avatars */
    private $avatars;

    /** @var HeadersFactory */
    private $headersFactory;

    public function __construct(
        Client $googleClient,
        Orm $orm,
        TokenUser $tokenUser,
        UserToAuthenticationArrayTransformer $userToAuthenticationArrayTransformer,
        Avatars $avatars,
        HeadersFactory $headersFactory
    ) {
        $this->googleClient = $googleClient;
        $this->orm = $orm;
        $this->tokenUser = $tokenUser;
        $this->userToAuthenticationArrayTransformer = $userToAuthenticationArrayTransformer;
        $this->avatars = $avatars;
        $this->headersFactory = $headersFactory;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $headers = $this->headersFactory->createDefault();

        try {
            $userSession = $this->tokenUser->getUserSession();
        } catch (InvalidTokenException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 401, $headers->toArray());
        }

        if ($userSession === null) {
            return new EmptyResponse(401, $headers->toArray());
        }

        $body = (array)json_decode((string)$request->getBody(), true);

        if (!isset($body['token'])) {
            return new EmptyResponse(400, $headers->toArray());
        }

        $token = (string)$body['token'];

        $payload = $this->googleClient->verifyIdToken($token);

        if (!$payload) {
            return new EmptyResponse(401, $headers->toArray());
        }

        $existingUser = $this->orm->users->getBy(['googleId' => $payload['sub']]);

        if ($existingUser !== null) {
            return new EmptyResponse(401, $headers->toArray()); // todo maybe merge accounts?
        }

        $user = $userSession->user;

        $user->googleId = $payload['sub'];
        $user->googleEmail = $payload['email'];
        $user->googleName = $payload['name'];
        $this->orm->persistAndFlush($user);

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

        return new JsonResponse([
            'authentication' => $this->userToAuthenticationArrayTransformer->transform($userSession, $this->tokenUser->getJwt()),
        ], 200, $headers->toArray());
    }
}
