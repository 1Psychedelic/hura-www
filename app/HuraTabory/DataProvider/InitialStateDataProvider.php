<?php
declare(strict_types=1);

namespace HuraTabory\DataProvider;

use HuraTabory\DataProvider\Authentication\TokenUserDataProvider;
use HuraTabory\DataProvider\Home\ViewHomeDataProvider;
use HuraTabory\DataProvider\Notification\NotificationsDataProvider;
use Psr\Http\Message\ServerRequestInterface;

class InitialStateDataProvider
{
    /** @var ViewHomeDataProvider */
    private $viewHomeDataProvider;

    /** @var TokenUserDataProvider */
    private $tokenUserDataProvider;

    /** @var NotificationsDataProvider */
    private $notificationsDataProvider;

    public function __construct(
        ViewHomeDataProvider $viewHomeDataProvider,
        TokenUserDataProvider $tokenUserDataProvider,
        NotificationsDataProvider $notificationsDataProvider
    ) {
        $this->viewHomeDataProvider = $viewHomeDataProvider;
        $this->tokenUserDataProvider = $tokenUserDataProvider;
        $this->notificationsDataProvider = $notificationsDataProvider;
    }

    public function getData(ServerRequestInterface $request): array
    {
        $user = $this->tokenUserDataProvider->getUser($request);

        $data = $this->viewHomeDataProvider->getData($user, false);
        $data['authentication'] = $this->tokenUserDataProvider->getData($request);
        $data['notifications'] = $this->notificationsDataProvider->getData($user);
        $data['canonical'] = rtrim((string)$request->getUri()->withQuery(''), '/');

        return $data;
    }
}
