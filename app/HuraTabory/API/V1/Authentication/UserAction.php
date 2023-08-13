<?php
declare(strict_types=1);

namespace HuraTabory\API\V1\Authentication;

use HuraTabory\API\V1\Authentication\Exception\InvalidTokenException;
use HuraTabory\Http\HeadersFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use HuraTabory\API\V1\Authentication\Service\JwtService;
use HuraTabory\API\V1\Authentication\Service\TokenUser;
use HuraTabory\API\V1\Authentication\Transformer\UserToAuthenticationArrayTransformer;
use VCD2\Orm;
use VCD2\Users\Service\Passwords;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\JsonResponse;

class UserAction implements RequestHandlerInterface
{
    /** @var TokenUser */
    private $tokenUser;

    /** @var UserToAuthenticationArrayTransformer */
    private $userToAuthenticationArrayTransformer;

    /** @var HeadersFactory */
    private $headersFactory;

    public function __construct(
        TokenUser $tokenUser,
        UserToAuthenticationArrayTransformer $userToAuthenticationArrayTransformer,
        HeadersFactory $headersFactory
    ) {
        $this->tokenUser = $tokenUser;
        $this->userToAuthenticationArrayTransformer = $userToAuthenticationArrayTransformer;
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

        $jwt = $this->tokenUser->getJwt();

        return new JsonResponse([
            'authentication' => $this->userToAuthenticationArrayTransformer->transform($userSession, $jwt),
        ], $userSession === null ? 401 : 200, $headers->toArray());
    }
}
