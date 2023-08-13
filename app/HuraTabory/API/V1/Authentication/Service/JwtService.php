<?php
declare(strict_types=1);

namespace HuraTabory\API\V1\Authentication\Service;

use DateTimeImmutable;
use HuraTabory\API\V1\Authentication\Exception\InvalidTokenException;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Validation\Constraint\IdentifiedBy;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\ValidAt;
use Lcobucci\JWT\Validation\RequiredConstraintsViolated;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use VCD2\Orm;
use VCD2\Users\UserSession;

class JwtService
{
    /** @var Configuration */
    private $configuration;

    /** @var ServerRequestInterface */
    private $request;

    /** @var Orm */
    private $orm;

    public function __construct(Configuration $configuration, ServerRequestInterface $request, Orm $orm)
    {
        $this->configuration = $configuration;
        $this->request = $request;
        $this->orm = $orm;
    }

    public function buildJwt(int $userId, int $sessionId): string
    {
        $now = new DateTimeImmutable();

        return $this->configuration->builder()
            ->issuedBy($this->request->getUri()->getHost())
            ->permittedFor($this->request->getUri()->getHost())
            ->identifiedBy('hura-tabory')
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now)
            ->expiresAt($now->modify('+2 hours'))
            ->withClaim('userId', $userId)
            ->withClaim('tokenId', $sessionId)
            ->getToken($this->configuration->signer(), $this->configuration->signingKey())
            ->toString();
    }

    public function buildRefreshToken(int $userId, int $tokenId): string
    {
        $now = new DateTimeImmutable();

        return $this->configuration->builder()
            ->issuedBy($this->request->getUri()->getHost())
            ->permittedFor($this->request->getUri()->getHost())
            ->identifiedBy('hura-tabory')
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now)
            ->expiresAt($now->modify('+3 months'))
            ->withClaim('userId', $userId)
            ->withClaim('tokenId', $tokenId)
            ->getToken($this->configuration->signer(), $this->configuration->signingKey())
            ->toString();
    }

    /**
     * @param string $refreshToken
     * @param UserSession|null $userSession
     * @return string
     * @throws InvalidTokenException
     */
    public function refreshAccessToken(string $refreshToken, UserSession &$userSession = null): string
    {
        $token = null;
        $userSession = $this->getUserSessionFromRefreshToken($refreshToken, $token);
        if ($userSession === null || !$userSession->enabled) {
            throw new InvalidTokenException('Token not found.');
        }

        try {
            $this->validateRefreshToken($token);
        } catch (RequiredConstraintsViolated $e) {
            $userSession->enabled = false;
            $userSession->lastSeen = new DateTimeImmutable();
            $this->orm->persistAndFlush($userSession);

            throw new InvalidTokenException($e->getMessage(), 0, $e);
        }

        $userSession->lastSeen = new DateTimeImmutable();
        $this->orm->persistAndFlush($userSession);

        return $this->buildJwt($userSession->user->id, $userSession->id);
    }

    public function disableRefreshToken(string $refreshToken): void
    {
        $userSession = $this->getUserSessionFromRefreshToken($refreshToken);
        if ($userSession === null || !$userSession->enabled) {
            return;
        }

        $userSession->enabled = false;
        $userSession->lastSeen = new DateTimeImmutable();
        $this->orm->persistAndFlush($userSession);
    }

    /**
     * @param string $refreshToken
     * @param Token|null $token
     * @return UserSession|null
     * @throws InvalidTokenException
     */
    public function getUserSessionFromRefreshToken(string $refreshToken, Token &$token = null): ?UserSession
    {
        try {
            $token = $this->configuration->parser()->parse($refreshToken);
        } catch (Throwable $e) {
            throw new InvalidTokenException($e->getMessage(), 0, $e);
        }

        if (!$token->claims()->has('userId')) {
            throw new InvalidTokenException('Token is missing a user ID.');
        }

        if (!$token->claims()->has('tokenId')) {
            throw new InvalidTokenException('Token is missing a token ID.');
        }

        $userId = (int)$token->claims()->get('userId');
        $tokenId = (int)$token->claims()->get('tokenId');

        return $this->orm->userSessions->getBy(['user' => $userId, 'id' => $tokenId]);
    }

    /**
     * @param Token $token
     * @throws RequiredConstraintsViolated
     */
    private function validateRefreshToken(Token $token): void
    {
        $constraints = [];
        $constraints[] = new IdentifiedBy('hura-tabory');
        $constraints[] = new SignedWith($this->configuration->signer(), $this->configuration->signingKey());
        $constraints[] = new ValidAt(SystemClock::fromSystemTimezone());
        $this->configuration->validator()->assert($token, ...$constraints);
    }
}
