<?php

namespace VCD\Admin\Users\UI;

use Hafo\NetteBridge\Forms\FormFactory;
use Hafo\Persona\HumanAge;
use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use VCD2\Orm;

class UsersWithoutAccountControl extends Control {

    private $container;

    function __construct(ContainerInterface $container, $filters = [], $extra = []) {
        $this->container = $container;

        $this->onAnchor[] = function() use ($filters, $extra) {

            $orm = $this->container->get(Orm::class);

            $cond = [];
            foreach($filters as $key => $val) {
                if($val === '-1') {
                    $cond[$key . '!='] = NULL;
                } else if($val === 2) {
                    $cond[$key] = NULL;
                } else {
                    $cond[$key] = $val;
                }
            }

            $emails = $orm->users->findEmails();

            $otherUsers = $orm->applications->findGroupedByEmail($emails);

            $this->template->filters = $filters;
            $this->template->extra = $extra;
            $this->template->otherUsers = $otherUsers;

            $this->template->age = function($dateBorn) {
                return (new HumanAge($dateBorn))->yearsAt(new \DateTime);
            };

            $this->template->countApplications = function($email) use ($orm) {
                return count($orm->applications->findByEmail($email));
            };
        };
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->render();
    }

}
