<?php

namespace VCD\Admin\Invoices\UI;

use Hafo\DI\Container;
use Nette\Application\UI\Control;
use Nette\Utils\Paginator;
use Nextras\Orm\Collection\ICollection;
use VCD\Admin\Applications\UI\ApplicationsFiltersControl;
use VCD2\Orm;

class InvoicesControl extends Control {

    const ITEMS_PER_PAGE = 50;

    /** @var Orm */
    private $orm;

    function __construct(Container $container, $page = 1, $filters = []) {
        $this->orm = $container->get(Orm::class);

        $this->onAnchor[] = function() use ($page, $filters) {

            $paginator = new Paginator;

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

            $invoices = $this->orm->invoices->findBy($cond)->orderBy('id', ICollection::DESC);

            $paginator->setItemCount($invoices->count());
            $paginator->setItemsPerPage(self::ITEMS_PER_PAGE);
            $paginator->setPage($page);

            $invoices = $invoices->limitBy($paginator->getLength(), $paginator->getOffset());

            $this->template->paginator = $paginator;
            $this->template->list = $invoices;
            $this->template->statusAllExceptUnfinished = ApplicationsFiltersControl::STATUS_ALL_EXCEPT_UNFINISHED;
        };
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->render();
    }

}
