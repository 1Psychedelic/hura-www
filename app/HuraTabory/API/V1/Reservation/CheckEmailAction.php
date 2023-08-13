<?php
declare(strict_types=1);

namespace HuraTabory\API\V1\Reservation;

use HuraTabory\API\V1\Authentication\Exception\InvalidTokenException;
use HuraTabory\Http\HeadersFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use HuraTabory\API\V1\Authentication\Service\TokenUser;
use VCD2\Orm;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\JsonResponse;

class CheckEmailAction implements RequestHandlerInterface
{
    /** @var TokenUser */
    private $tokenUser;

    /** @var Orm */
    private $orm;

    /** @var HeadersFactory */
    private $headersFactory;

    public function __construct(TokenUser $tokenUser, Orm $orm, HeadersFactory $headersFactory)
    {
        $this->tokenUser = $tokenUser;
        $this->orm = $orm;
        $this->headersFactory = $headersFactory;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $headers = $this->headersFactory->createDefault();

        $body = (array)json_decode((string)$request->getBody(), true);

        if (!isset($body['email'])) {
            return new EmptyResponse(400, $headers->toArray());
        }

        $email = trim($body['email']);

        try {
            $apiUser = $this->tokenUser->getUser();
        } catch (InvalidTokenException $e) {
            return new EmptyResponse(401, $headers->toArray());
        }
        $user = $this->orm->users->getByEmail($email);

        if ($user !== null && ($apiUser === null || $user->id !== $apiUser->id)) {
            return new JsonResponse([
                'email' => $user->email,
                'wasAlreadyRegistered' => true,
            ], 200, $headers->toArray());
        }

        return new JsonResponse([
            'email' => $email,
            'wasAlreadyRegistered' => false,
        ], 200, $headers->toArray());
    }
}
