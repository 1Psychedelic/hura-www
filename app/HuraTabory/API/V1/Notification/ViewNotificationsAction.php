<?php
declare(strict_types=1);

namespace HuraTabory\API\V1\Notification;

use HuraTabory\API\V1\Authentication\Exception\InvalidTokenException;
use HuraTabory\API\V1\Authentication\Service\TokenUser;
use HuraTabory\DataProvider\Notification\NotificationsDataProvider;
use HuraTabory\Http\HeadersFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\JsonResponse;

class ViewNotificationsAction implements RequestHandlerInterface
{
    /** @var TokenUser */
    private $tokenUser;

    /** @var NotificationsDataProvider */
    private $notificationsDataProvider;

    /** @var HeadersFactory */
    private $headersFactory;

    public function __construct(
        TokenUser $tokenUser,
        NotificationsDataProvider $notificationsDataProvider,
        HeadersFactory $headersFactory
    ) {
        $this->tokenUser = $tokenUser;
        $this->notificationsDataProvider = $notificationsDataProvider;
        $this->headersFactory = $headersFactory;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $headers = $this->headersFactory->createDefault();

        try {
            $user = $this->tokenUser->getUser();
        } catch (InvalidTokenException $e) {
            return new EmptyResponse(403, $headers->toArray());
        }

        if ($user === null) {
            return new EmptyResponse(403, $headers->toArray());
        }

        $data = $this->notificationsDataProvider->getData($user);

        return new JsonResponse(['notifications' => $data], 200, $headers->toArray());
    }
}
