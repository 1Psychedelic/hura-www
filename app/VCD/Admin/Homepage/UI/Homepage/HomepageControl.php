<?php
declare(strict_types=1);

namespace VCD\Admin\Homepage\UI;

use Hafo\DI\Container;
use HuraTabory\Domain\Homepage\HomepageConfig;
use HuraTabory\Domain\Homepage\HomepageRepository;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use VCD2\UI\Admin\Forms\AdminFormRenderer;

class HomepageControl extends Control
{
    /** @var Container */
    private $container;

    public function __construct(Container $container)
    {
        parent::__construct();
        $this->container = $container;

        $this->onAnchor[] = function () use ($container) {

            $f = new Form();
            $f->setRenderer(new AdminFormRenderer());

            $f->addCheckboxList('enabledSections', 'Sekce na homepage', HomepageConfig::AVAILABLE_SECTIONS);

            $f->addSubmit('save', 'Uložit');

            $f->onSuccess[] = function (Form $form) use ($container) {
                if ($form->isSubmitted() === $form['save']) {
                    $data = $form->getValues(true);

                    $homepageConfig = new HomepageConfig($data['enabledSections']);
                    $container->get(HomepageRepository::class)->saveHomepageConfig($homepageConfig);

                    $this->presenter->flashMessage('Uloženo.', 'success');
                    $this->presenter->redirect('this');
                }

            };

            $homepageConfig = $container->get(HomepageRepository::class)->getHomepageConfig();
            \Tracy\Debugger::barDump($homepageConfig);
            $f['enabledSections']->setValue($homepageConfig->getEnabledSections());

            $this->addComponent($f, 'form');
        };
    }

    public function render(): void
    {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->render();
    }
}
