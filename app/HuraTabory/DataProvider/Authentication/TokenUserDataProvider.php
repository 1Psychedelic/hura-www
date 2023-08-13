<?php
declare(strict_types=1);

namespace HuraTabory\DataProvider\Authentication;

use HuraTabory\API\V1\Authentication\Exception\InvalidTokenException;
use HuraTabory\API\V1\Authentication\Service\JwtService;
use HuraTabory\API\V1\Authentication\Transformer\UserToAuthenticationArrayTransformer;
use Psr\Http\Message\ServerRequestInterface;
use VCD2\Users\User;
use VCD2\Users\UserSession;
use Zend\Diactoros\Response\EmptyResponse;

class TokenUserDataProvider
{
    /** @var JwtService */
    private $jwtService;

    /** @var UserToAuthenticationArrayTransformer */
    private $userToAuthenticationArrayTransformer;

    /** @var bool */
    private $isInitialized = false;

    /** @var UserSession|null */
    private $userSession;

    /** @var string */
    private $accessToken = '';

    public function __construct(JwtService $jwtService, UserToAuthenticationArrayTransformer $userToAuthenticationArrayTransformer)
    {
        $this->jwtService = $jwtService;
        $this->userToAuthenticationArrayTransformer = $userToAuthenticationArrayTransformer;
    }

    public function getUser(ServerRequestInterface $request): ?User
    {
        $userSession = $this->getUserSession($request);

        return $userSession === null ? null : $userSession->user;
    }

    public function getUserSession(ServerRequestInterface $request): ?UserSession
    {
        if ($this->isInitialized) {
            return $this->userSession;
        }

        $this->isInitialized = true;

        $cookies = $request->getCookieParams();

        if (!isset($cookies['refreshToken'])) {
            return null;
        }

        /** @var UserSession|null $userSession */
        $userSession = null;
        try {
            $accessToken = $this->jwtService->refreshAccessToken((string)$cookies['refreshToken'], $userSession);
        } catch (InvalidTokenException $e) {
            return null;
        }

        if ($userSession === null || !$userSession->enabled) {
            return null;
        }

        $this->userSession = $userSession;
        $this->accessToken = $accessToken;

        return $this->userSession;
    }

    public function getData(ServerRequestInterface $request): array
    {
        $userSession = $this->getUserSession($request);

        if ($userSession === null) {
            return $this->userToAuthenticationArrayTransformer->transform(null, '');
        }

        return $this->userToAuthenticationArrayTransformer->transform($userSession, $this->accessToken);
    }
}
