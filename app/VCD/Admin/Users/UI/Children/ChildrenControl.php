<?php

namespace VCD\Admin\Users\UI;

use Hafo\NetteBridge\Forms\FormFactory;
use Hafo\Persona\HumanAge;
use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Utils\Paginator;
use Nextras\Orm\Collection\ICollection;
use Tomaj\Form\Renderer\BootstrapInlineRenderer;
use VCD2\Orm;
use VCD2\Users\Child;

class ChildrenControl extends Control {

    const ITEMS_PER_PAGE = 20;

    private $container;

    function __construct(ContainerInterface $container, $filters = [], $page = 1, $q = NULL) {
        $this->container = $container;
        $this->onAnchor[] = function() use ($filters, $page, $q) {
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

            /** @var Child[]|ICollection $children */
            $children = $orm->children->search($q)->findBy($cond)->orderBy('id', ICollection::DESC);

            $paginator->setItemCount($children->count());
            $paginator->setItemsPerPage(self::ITEMS_PER_PAGE);
            $paginator->setPage($page);

            $children = $children->limitBy($paginator->getLength(), $paginator->getOffset());

            $this->template->paginator = $paginator;
            $this->template->filters = $filters;
            $this->template->children = $children;
            $this->template->age = function($dateBorn) {
                return (new HumanAge($dateBorn))->yearsAt(new \DateTime);
            };

            $f = $this->container->get(FormFactory::class)->create();
            $f->addText('q', '')
                ->setDefaultValue($q)
                ->getControlPrototype()->addAttributes(['placeholder' => 'Jméno']);
            $f->addSubmit('search', 'Hledat');
            $f->setRenderer(new BootstrapInlineRenderer);
            $f->onSuccess[] = function(Form $f) {
                if($f->isSubmitted() === $f['search']) {
                    $this->presenter->redirect('this', ['q' => $f->getValues()->q, 'page' => 1]);
                }
            };
            $this->addComponent($f, 'search');
        };
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->render();
    }

}
