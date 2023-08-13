<?php
declare(strict_types=1);

namespace HuraTabory\API\V1\Authentication;

use Hafo\Security\Storage\Emails;
use Hafo\Security\Storage\Passwords;
use Hafo\Security\Storage\Users;
use HuraTabory\API\V1\Authentication\Exception\InvalidTokenException;
use HuraTabory\API\V1\Authentication\Service\TokenUser;
use HuraTabory\Http\HeadersFactory;
use Nette\Utils\Validators;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use VCD2\Emails\Service\Emails\EmailVerifyMail;
use VCD2\Orm;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\JsonResponse;

class RegisterAction implements RequestHandlerInterface
{
    /** @var TokenUser */
    private $tokenUser;

    /** @var Passwords */
    private $passwords;

    /** @var Orm */
    private $orm;

    /** @var Users */
    private $users;

    /** @var Emails */
    private $emails;

    /** @var EmailVerifyMail */
    private $emailVerifyMail;

    /** @var HeadersFactory */
    private $headersFactory;

    public function __construct(
        TokenUser $tokenUser,
        Passwords $passwords,
        Orm $orm,
        Users $users,
        Emails $emails,
        EmailVerifyMail $emailVerifyMail,
        HeadersFactory $headersFactory
    ) {
        $this->tokenUser = $tokenUser;
        $this->passwords = $passwords;
        $this->orm = $orm;
        $this->users = $users;
        $this->emails = $emails;
        $this->emailVerifyMail = $emailVerifyMail;
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

        if (!isset($body['email'], $body['name'], $body['password'])) {
            return new EmptyResponse(400, $headers->toArray());
        }

        if (!Validators::isEmail((string)$body['email'])) {
            return new EmptyResponse(400, $headers->toArray());
        }

        $user = $this->orm->users->getByEmail((string)$body['email']);
        if ($user !== null) {
            return new JsonResponse([
                'error' => 'Účet se zadanou e-mailovou adresou je již registrován.',
            ], 403);
        }

        $userId = $this->users->register((string)$body['email'], ['name' => (string)$body['name']]);
        $this->passwords->setPassword($userId, (string)$body['password']);
        $hash = $this->emails->requestEmailVerifyHash((string)$body['email']);
        $this->emailVerifyMail->send((string)$body['email'], $hash);

        return new EmptyResponse(201, $headers->toArray());
    }
}
