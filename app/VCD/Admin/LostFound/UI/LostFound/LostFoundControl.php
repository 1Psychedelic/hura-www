<?php

namespace VCD\Admin\LostFound\UI;

use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use Nette\Database\Context;
use VCD2\Orm;
use VCD2\Users\User;

class LostFoundControl extends Control {

    private $container;

    function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    function handleLostFoundSolve($id) {
        $this->container->get(Context::class)->table('vcd_photo')->where('type = 1 AND claimed_by IS NOT NULL AND id = ?', $id)
            ->update([
                'visible' => FALSE
            ]);
        $this->presenter->flashMessage('Uloženo.', 'success');
        $this->presenter->redirect('this');
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $users = $this->container->get(Orm::class)->users;
        $lostFound = [];
        foreach($this->container->get(Context::class)->table('vcd_photo')->where('visible = 1 AND type = 1 AND claimed_by IS NOT NULL')->fetchAll() as $item) {
            /** @var User $user */
            $user = $users->get($item['claimed_by']);
            $lostFound[] = [
                'id' => $item['id'],
                'event' => $item['event'],
                'eventname' => $item['event'] === NULL ? 'Nezařazené' : $item->ref('vcd_event', 'event')['name'],
                'name' => $item['name'],
                'user' => $item['claimed_by'],
                'username' => $user->name,
                'email' => $user->email
            ];
        }
        $this->template->lostFound = $lostFound;
        $this->template->render();
    }

}
