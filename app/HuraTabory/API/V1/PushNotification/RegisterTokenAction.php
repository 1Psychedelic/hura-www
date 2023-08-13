<?php
declare(strict_types=1);

namespace HuraTabory\API\V1\PushNotification;

use HuraTabory\API\V1\Authentication\Exception\InvalidTokenException;
use HuraTabory\API\V1\Authentication\Service\TokenUser;
use HuraTabory\Http\HeadersFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use VCD2\Firebase\FirebasePushToken;
use VCD2\Orm;
use Zend\Diactoros\Response\EmptyResponse;

class RegisterTokenAction implements RequestHandlerInterface
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

        try {
            $user = $this->tokenUser->getUser();
        } catch (InvalidTokenException $e) {
            return new EmptyResponse(401, $headers->toArray());
        }

        if ($user === null) {
            return new EmptyResponse(401, $headers->toArray());
        }

        $body = json_decode((string)$request->getBody(), true);
        if (!isset($body['token'])) {
            return new EmptyResponse(400, $headers->toArray());
        }

        foreach ($user->firebasePushTokens as $token) {
            if ($token->token === $body['token']) {
                return new EmptyResponse(200, $headers->toArray());
            }
        }

        $token = new FirebasePushToken($user, $body['token']);
        $this->orm->persistAndFlush($token);

        return new EmptyResponse(201, $headers->toArray());
    }
}
