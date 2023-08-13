<?php

namespace VCD\Admin\Users\UI;

use Hafo\NetteBridge\Forms\FormFactory;
use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use Nette\Http\Session;
use Nette\Utils\Paginator;
use Nextras\Orm\Collection\ICollection;
use VCD2\Orm;
use VCD2\Users\Consent;

class ConsentsControl extends Control {

    const ITEMS_PER_PAGE = 20;

    private $container;

    function __construct(ContainerInterface $container, $filters = [], $extra = [], $page = 1) {
        $this->container = $container;

        $this->onAnchor[] = function() use ($filters, $extra, $page) {

            $orm = $this->container->get(Orm::class);

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

            /** @var Consent[]|ICollection $consents */
            $consents = $orm->consents->findBy($cond)->orderBy('id', ICollection::DESC);

            $paginator->setItemCount($consents->count());
            $paginator->setItemsPerPage(self::ITEMS_PER_PAGE);
            $paginator->setPage($page);

            $consents = $consents->limitBy($paginator->getLength(), $paginator->getOffset());

            $this->template->filters = $filters;
            $this->template->extra = $extra;
            $this->template->list = $consents;
            $this->template->paginator = $paginator;

            $this->template->types = Consent::TYPES_NAMES;
        };
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->fakeLogged = isset($this->container->get(Session::class)->getSection('vcd.security.fakeLogin')['originalUser']);
        $this->template->render();
    }

}
