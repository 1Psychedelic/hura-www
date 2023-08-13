<?php
declare(strict_types=1);

namespace VCD2\UI\Admin\Filters;

use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use VCD\Admin\SavedFilters;
use VCD2\Orm;
use VCD2\UI\Admin\Forms\AdminFormRenderer;

abstract class AdminFiltersControl extends Control
{
    /** @var ContainerInterface */
    protected $container;

    /** @var Orm */
    protected $orm;

    /** @var array */
    protected $httpData;

    /** @var int|null */
    protected $savedFilter;

    /** @var bool */
    protected $pinnedFilters;

    public function __construct(ContainerInterface $container, array $httpData = [], ?int $savedFilter = null, bool $pinnedFilters = false)
    {
        $this->container = $container;
        $this->httpData = array_filter($httpData);
        $this->savedFilter = $savedFilter;
        $this->pinnedFilters = $pinnedFilters;

        if ($savedFilter !== null) {
            $this->httpData = $this->container->get(SavedFilters::class)->getFilter($savedFilter);
        }

        $this->onAnchor[] = function () {
            $this->orm = $this->container->get(Orm::class);

            $this->addComponent($this->createForm(), 'form');
            $this->addComponent($this->createSaveFilterForm(), 'saveFilterForm');
        };
    }

    abstract protected function createForm(): Form;

    abstract protected function getGroupName(): string;

    abstract public function createQueryFilters(): array;

    private function createSaveFilterForm(): Form
    {
        $f = new Form;

        $f->setRenderer(new AdminFormRenderer);

        $f->addText('name', 'Název');
        $f->addSubmit('save', 'Uložit');

        $f->onSuccess[] = function (Form $f) {
            if ($f->isSubmitted() === $f['save']) {
                $data = $f->getValues(true);
                $id = $this->container->get(SavedFilters::class)->saveFilter($this->getGroupName(), $data['name'], $this->httpData);
                $this->presenter->redirect('this', ['filters' => [], 'savedFilter' => $id]);
            }
            if ($f->isSubmitted() === $f['delete'] && $this->savedFilter !== null) {
                $this->container->get(SavedFilters::class)->deleteFilter($this->savedFilter);
                $this->presenter->redirect('this', ['filters' => $this->httpData, 'savedFilter' => null]);
            }
        };

        return $f;
    }

    public function handleDeleteFilter($id)
    {
        $this->container->get(SavedFilters::class)->deleteFilter((int)$id);
        $this->presenter->redirect('this', ['savedFilter' => null, 'filters' => $this->httpData]);
    }

    public function render()
    {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->httpData = $this->httpData;
        $this->template->savedFilters = $this->container->get(SavedFilters::class)->getFilters($this->getGroupName());
        $this->template->savedFilter = $this->savedFilter;
        $this->template->pinnedFilters = $this->pinnedFilters;
        $this->template->render();
    }
}
