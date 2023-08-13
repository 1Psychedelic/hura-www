<?php
declare(strict_types=1);

namespace HuraTabory\API\V1\Authentication;

use Hafo\DI\Container;
use HuraTabory\API\V1\Authentication\Exception\InvalidTokenException;
use HuraTabory\DataProvider\Notification\NotificationsDataProvider;
use HuraTabory\Http\HeadersFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use HuraTabory\API\V1\Authentication\Service\JwtService;
use HuraTabory\API\V1\Authentication\Transformer\UserToAuthenticationArrayTransformer;
use Zend\Diactoros\Response\JsonResponse;

class LogoutAction implements RequestHandlerInterface
{
    /** @var JwtService */
    private $jwtService;

    /** @var UserToAuthenticationArrayTransformer */
    private $userToAuthenticationArrayTransformer;

    /** @var HeadersFactory */
    private $headersFactory;

    /** @var NotificationsDataProvider */
    private $notificationsDataProvider;

    public function __construct(
        JwtService $jwtService,
        UserToAuthenticationArrayTransformer $userToAuthenticationArrayTransformer,
        HeadersFactory $headersFactory,
        NotificationsDataProvider $notificationsDataProvider
    ) {
        $this->jwtService = $jwtService;
        $this->userToAuthenticationArrayTransformer = $userToAuthenticationArrayTransformer;
        $this->headersFactory = $headersFactory;
        $this->notificationsDataProvider = $notificationsDataProvider;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $headers = $this->headersFactory
            ->createDefault()
            ->withoutCookie($this->headersFactory->getRefreshTokenCookieTemplate());

        $response = new JsonResponse([
            'authentication' => $this->userToAuthenticationArrayTransformer->transform(null, ''),
            'notifications' => $this->notificationsDataProvider->getData(null),
        ], 200, $headers->toArray());


        $cookies = $request->getCookieParams();
        if (!isset($cookies['refreshToken'])) {
            return $response;
        }

        $refreshToken = (string)$cookies['refreshToken'];

        try {
            $this->jwtService->disableRefreshToken($refreshToken);
        } catch (InvalidTokenException $e) {
            // this is fine
        }

        return $response;
    }
}
