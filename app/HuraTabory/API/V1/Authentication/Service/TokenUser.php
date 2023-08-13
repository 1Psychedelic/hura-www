<?php
declare(strict_types=1);

namespace HuraTabory\API\V1\Authentication\Service;

use HuraTabory\API\V1\Authentication\Exception\InvalidTokenException;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Validation\Constraint\IdentifiedBy;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\ValidAt;
use Lcobucci\JWT\Validation\RequiredConstraintsViolated;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use VCD2\Orm;
use VCD2\Users\User;
use VCD2\Users\UserSession;

class TokenUser
{
    /** @var ServerRequestInterface */
    private $request;

    /** @var Configuration */
    private $configuration;

    /** @var bool */
    private $isInitialized = false;

    /** @var UserSession|null */
    private $apiUserSession = null;

    /** @var Orm */
    private $orm;

    public function __construct(ServerRequestInterface $request, Configuration $configuration, Orm $orm)
    {
        $this->request = $request;
        $this->configuration = $configuration;
        $this->orm = $orm;
    }

    public function getJwt(): string
    {
        return str_replace('Bearer ', '', $this->request->getHeaderLine('Authorization'));
    }

    /**
     * @return User|null
     * @throws InvalidTokenException
     */
    public function getUser(): ?User
    {
        $userSession = $this->getUserSession();

        return $userSession === null ? null : $userSession->user;
    }

    public function getUserSession(): ?UserSession
    {
        if ($this->isInitialized) {
            return $this->apiUserSession;
        }

        $jwt = $this->getJwt();
        if ($jwt === '') {
            $this->isInitialized = true;

            return null;
        }

        try {
            $token = $this->configuration->parser()->parse($jwt);
        } catch (Throwable $e) {
            throw new InvalidTokenException('Unable to parse token.', 0, $e);
        }

        $constraints = [];
        $constraints[] = new IdentifiedBy('hura-tabory');
        $constraints[] = new SignedWith($this->configuration->signer(), $this->configuration->signingKey());
        $constraints[] = new ValidAt(SystemClock::fromSystemTimezone());
        try {
            $this->configuration->validator()->assert($token, ...$constraints);
        } catch (RequiredConstraintsViolated $e) {
            throw new InvalidTokenException($e->getMessage(), 0, $e);
        }

        if (!$token->claims()->has('userId')) {
            throw new InvalidTokenException('Token is missing a user ID.');
        }
        if (!$token->claims()->has('tokenId')) {
            throw new InvalidTokenException('Token is missing a token ID.');
        }

        $userId = (int)$token->claims()->get('userId');
        $sessionId = (int)$token->claims()->get('tokenId');

        $this->apiUserSession = $this->orm->userSessions->getBy([
            'id' => $sessionId,
            'user' => $userId,
            'enabled' => true,
        ]);

        if ($this->apiUserSession === null) {
            throw new InvalidTokenException('User not found.');
        }

        $this->isInitialized = true;

        return $this->apiUserSession;
    }

    public function setUserSession(?UserSession $userSession): void
    {
        $this->apiUserSession = $userSession;

        $this->isInitialized = true;
    }
}
