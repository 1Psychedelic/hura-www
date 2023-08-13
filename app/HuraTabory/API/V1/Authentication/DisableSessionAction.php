<?php
declare(strict_types=1);

namespace HuraTabory\API\V1\Authentication;

use HuraTabory\API\V1\Authentication\Exception\InvalidTokenException;
use HuraTabory\API\V1\Authentication\Service\TokenUser;
use HuraTabory\API\V1\Authentication\Transformer\UserSessionTransformer;
use HuraTabory\Http\HeadersFactory;
use Nextras\Orm\Collection\ICollection;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use VCD2\Orm;
use VCD2\Users\UserSession;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\JsonResponse;

class DisableSessionAction implements RequestHandlerInterface
{
    /** @var TokenUser */
    private $tokenUser;

    /** @var Orm */
    private $orm;

    /** @var UserSessionTransformer */
    private $userSessionTransformer;

    /** @var HeadersFactory */
    private $headersFactory;

    public function __construct(
        TokenUser $tokenUser,
        Orm $orm,
        UserSessionTransformer $userSessionTransformer,
        HeadersFactory $headersFactory
    ) {
        $this->tokenUser = $tokenUser;
        $this->orm = $orm;
        $this->userSessionTransformer = $userSessionTransformer;
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

        $body = (array)json_decode((string)$request->getBody(), true);

        if (!isset($body['sessionId'])) {
            return new EmptyResponse(400, $headers->toArray());
        }

        $currentSession = $this->tokenUser->getUserSession();

        if ($currentSession === null) {
            return new EmptyResponse(401, $headers->toArray());
        }

        $sessionId = (int)$body['sessionId'];

        $userSession = $this->orm->userSessions->getBy([
            'id' => $sessionId,
            'user' => $user->id
        ]);

        if ($userSession === null || $userSession->id === $currentSession->id) {
            return new EmptyResponse(404);
        }

        $userSession->enabled = false;
        $this->orm->persistAndFlush($userSession);

        $userSessions = [];
        $sessions = $this->orm->userSessions
            ->findBy(['user' => $user->id, 'enabled' => true])
            ->orderBy('lastSeen', ICollection::DESC);
        foreach ($sessions as $userSession) {
            /** @var UserSession $userSession */
            $userSessions[] = $this->userSessionTransformer->transform($userSession, $currentSession);
        }

        return new JsonResponse([
            'userSessions' => $userSessions,
        ], 200 , $headers->toArray());
    }
}
