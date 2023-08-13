<?php

namespace VCD\Admin\Payments\UI;

use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Utils\Paginator;
use Nextras\Orm\Collection\ICollection;
use VCD2\Orm;

class PaymentsControl extends Control {

    const ITEMS_PER_PAGE = 50;

    private $container;

    function __construct(ContainerInterface $container, $page = 1, $application = NULL) {
        $this->container = $container;

        $this->onAnchor[] = function() use ($page, $application) {

            $paginator = new Paginator;

            $cond = [];
            if($application !== NULL) {
                $cond['application'] = $application;
            }

            $orm = $this->container->get(Orm::class);
            $payments = $orm->payments->findBy($cond)->orderBy('id', ICollection::DESC);

            $paginator->setItemCount($payments->count());
            $paginator->setItemsPerPage(self::ITEMS_PER_PAGE);
            $paginator->setPage($page);

            $payments = $payments->limitBy($paginator->getLength(), $paginator->getOffset());

            $this->template->paginator = $paginator;
            $this->template->list = $payments;
        };
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->render();
    }

}
