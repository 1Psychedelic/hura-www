<?php

namespace VCD\Admin\Newsletter\UI;

use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nextras\Orm\Collection\ICollection;
use VCD2\Events\Event;
use VCD2\Orm;

class VipUsersControl extends Control {

    private $container;

    function __construct(ContainerInterface $container, $divider = ';') {
        $this->container = $container;

        $this->onAnchor[] = function() use ($divider) {

            $orm = $this->container->get(Orm::class);

            $emails = [];

            $users = $orm->users->findAll();
            foreach($users as $user) {
                if($user->isVip) {
                    $emails[$user->email] = true;
                }
            }

            $this->template->emails = $emails;
            $this->template->divider = $divider;
        };
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->render();
    }

    private function db() {
        return $this->container->get(Context::class);
    }

}
