<?php
declare(strict_types=1);

namespace HuraTabory\API\V1\Authentication;

use Hafo\DI\Container;
use HuraTabory\API\V1\Authentication\Service\TokenUser;
use HuraTabory\DataProvider\Notification\NotificationsDataProvider;
use HuraTabory\Http\HeadersFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use HuraTabory\API\V1\Authentication\Service\JwtService;
use HuraTabory\API\V1\Authentication\Transformer\UserToAuthenticationArrayTransformer;
use VCD2\Orm;
use VCD2\Users\Service\Passwords;
use VCD2\Users\Service\UserSessions;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\JsonResponse;

class LoginAction implements RequestHandlerInterface
{
    /** @var Passwords */
    private $passwords;

    /** @var Orm */
    private $orm;

    /** @var JwtService */
    private $jwtService;

    /** @var UserToAuthenticationArrayTransformer */
    private $userToAuthenticationArrayTransformer;

    /** @var UserSessions */
    private $userSessions;

    /** @var TokenUser */
    private $tokenUser;

    /** @var HeadersFactory */
    private $headersFactory;

    /** @var NotificationsDataProvider */
    private $notificationsDataProvider;

    public function __construct(
        Passwords $passwords,
        Orm $orm,
        JwtService $jwtService,
        UserToAuthenticationArrayTransformer $userToAuthenticationArrayTransformer,
        UserSessions $userSessions,
        TokenUser $tokenUser,
        HeadersFactory $headersFactory,
        NotificationsDataProvider $notificationsDataProvider
    ) {
        $this->passwords = $passwords;
        $this->orm = $orm;
        $this->jwtService = $jwtService;
        $this->userToAuthenticationArrayTransformer = $userToAuthenticationArrayTransformer;
        $this->userSessions = $userSessions;
        $this->tokenUser = $tokenUser;
        $this->headersFactory = $headersFactory;
        $this->notificationsDataProvider = $notificationsDataProvider;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $headers = $this->headersFactory->createDefault();

        $body = (array)json_decode((string)$request->getBody(), true);

        if (!isset($body['login'], $body['password'])) {
            return new EmptyResponse(400, $headers->toArray());
        }

        $userId = $this->passwords->verifyPassword($body['login'], $body['password']);
        if ($userId === false) {
            return new EmptyResponse(401, $headers->toArray());
        }

        $user = $this->orm->users->get($userId);
        if ($user === null) {
            return new EmptyResponse(401, $headers->toArray());
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
