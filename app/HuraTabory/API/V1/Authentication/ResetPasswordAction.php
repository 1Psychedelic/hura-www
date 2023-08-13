<?php
declare(strict_types=1);

namespace HuraTabory\API\V1\Authentication;

use Hafo\Security\Storage\Passwords;
use HuraTabory\API\V1\Authentication\Exception\InvalidTokenException;
use HuraTabory\API\V1\Authentication\Service\TokenUser;
use HuraTabory\Http\HeadersFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\JsonResponse;

class ResetPasswordAction implements RequestHandlerInterface
{
    /** @var TokenUser */
    private $tokenUser;

    /** @var Passwords */
    private $passwords;

    /** @var HeadersFactory */
    private $headersFactory;

    public function __construct(TokenUser $tokenUser, Passwords $passwords, HeadersFactory $headersFactory)
    {
        $this->tokenUser = $tokenUser;
        $this->passwords = $passwords;
        $this->headersFactory = $headersFactory;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $headers = $this->headersFactory->createDefault();

        try {
            $user = $this->tokenUser->getUser();
        } catch (InvalidTokenException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 401, $headers->toArray());
        }

        if ($user !== null) {
            return new EmptyResponse(401, $headers->toArray());
        }

        $body = (array)json_decode((string)$request->getBody(), true);

        if (!isset($body['password'], $body['hash'])) {
            return new EmptyResponse(400, $headers->toArray());
        }

        $userId = $this->passwords->isPasswordResetHashValid((string)$body['hash']);
        if ($userId === false) {
            return new EmptyResponse(403, $headers->toArray());
        }

        $this->passwords->setPassword($userId, (string)$body['password']);

        return new EmptyResponse(200, $headers->toArray());
    }
}
