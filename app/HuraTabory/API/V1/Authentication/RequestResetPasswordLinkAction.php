<?php
declare(strict_types=1);

namespace HuraTabory\API\V1\Authentication;

use Hafo\Security\Storage\Passwords;
use HuraTabory\API\V1\Authentication\Exception\InvalidTokenException;
use HuraTabory\API\V1\Authentication\Service\TokenUser;
use HuraTabory\Http\HeadersFactory;
use Nette\Utils\Random;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use VCD2\Emails\Service\Emails\PasswordChangeMail;
use VCD2\Orm;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\JsonResponse;

class RequestResetPasswordLinkAction implements RequestHandlerInterface
{
    /** @var TokenUser */
    private $tokenUser;

    /** @var Orm */
    private $orm;

    /** @var PasswordChangeMail */
    private $passwordChangeMail;

    /** @var HeadersFactory */
    private $headersFactory;

    public function __construct(
        TokenUser $tokenUser,
        Orm $orm,
        PasswordChangeMail $passwordChangeMail,
        HeadersFactory $headersFactory
    ) {
        $this->tokenUser = $tokenUser;
        $this->orm = $orm;
        $this->passwordChangeMail = $passwordChangeMail;
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
            return new EmptyResponse(403, $headers->toArray());
        }

        $body = (array)json_decode((string)$request->getBody(), true);

        if (!isset($body['email'])) {
            return new EmptyResponse(400, $headers->toArray());
        }

        $user = $this->orm->users->getByEmail((string)$body['email']);
        if ($user !== null) {
            if($user->passwordRestore === NULL) {
                $user->passwordRestore = Random::generate(40);
                $this->orm->persistAndFlush($user);
            }
            $this->passwordChangeMail->send($user->id);
        }

        return new EmptyResponse(200, $headers->toArray());
    }
}
