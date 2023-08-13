<?php

namespace VCD\Admin\Applications\UI;

use Nette\Application\BadRequestException;
use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use VCD2\Orm;
use VCD2\UI\Admin\Forms\AdminFormRenderer;

class ApplicationInternalNoteControl extends Control {

    private $container;

    function __construct(ContainerInterface $container, $id) {
        $this->container = $container;

        $this->onAnchor[] = function() use ($id) {

            $orm = $this->container->get(Orm::class);

            $application = $orm->applications->get($id);
            if ($application === null) {
                throw new BadRequestException();
            }

            $this->template->application = $application;

            $f = new Form;
            $f->setRenderer(new AdminFormRenderer);

            $f->addTextArea('internalNotes', 'Interní poznámka')
                ->setNullable()
                ->getControlPrototype()
                ->addAttributes(['rows' => 8]);

            $f->addProtection();
            $f->addSubmit('save', 'Uložit');

            $f->onSuccess[] = function(Form $f) use ($orm, $application) {
                if($f->isSubmitted() === $f['save']) {

                    $data = $f->getValues(TRUE);
                    $application->internalNotes = $data['internalNotes'];
                    $orm->persistAndFlush($application);

                    $this->presenter->flashMessage('Uloženo.', 'success');
                    $this->presenter->redirect('this');
                }
            };

            $f->setValues(['internalNotes' => $application->internalNotes]);
            $this->addComponent($f, 'form');
        };
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->render();
    }
}
