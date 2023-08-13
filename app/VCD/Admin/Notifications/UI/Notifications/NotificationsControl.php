<?php

namespace VCD\Admin\Notifications\UI;

use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use Nette\Caching\Cache;
use Nette\Database\Context;
use Nette\Security\User;
use VCD\Admin\Applications\UI\ApplicationsFiltersControl;
use VCD\Notifications\DefaultModel\CachedNotifications;
use VCD\Notifications\Notifications;
use VCD2\Orm;

class NotificationsControl extends Control {

    private $container;

    function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    function handleNotificationsRead() {
        $this->db()->table('vcd_notification')->where('recipient = ?', $this->container->get(User::class)->id)->update(['is_read' => 1]);
        $this->container->get(Cache::class)->clean([Cache::TAGS => [CachedNotifications::CACHE_TAG]]);
        $this->presenter->flashMessage('Notifikace byly označeny jako přečtené.', 'success');
        $this->presenter->redirect('this');
    }

    function handleNotificationRead($id) {
        $this->db()->table('vcd_notification')->where('recipient = ? AND id = ?', [$this->container->get(User::class)->id, $id])->update(['is_read' => 1]);
        $this->container->get(Cache::class)->clean([Cache::TAGS => [CachedNotifications::CACHE_TAG]]);
        $this->presenter->flashMessage('Notifikace byla označena jako přečtená.', 'success');
        $this->presenter->redirect('this');
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->notifications = $this->container->get(Notifications::class)->load();
        $this->template->users = $this->container->get(Orm::class)->users->findIdNamePairs();
        $this->template->statusAllExceptUnfinished = ApplicationsFiltersControl::STATUS_ALL_EXCEPT_UNFINISHED;
        $this->template->render();
    }

    private function db() {
        return $this->container->get(Context::class);
    }

}
