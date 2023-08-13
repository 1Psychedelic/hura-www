<?php

namespace VCD\UI\FrontModule\UserModule;

use Hafo\DI\Container;
use Hafo\NetteBridge\Forms\FormFactory;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Tomaj\Form\Renderer\BootstrapRenderer;
use Tomaj\Form\Renderer\BootstrapVerticalRenderer;
use VCD2\PostOffice\Letter;
use VCD2\PostOffice\NoCurrentEventException;
use VCD2\PostOffice\Service\PostOffice;

class PostOfficePresenter extends BasePresenter {

    const LINK_DEFAULT = ':Front:User:PostOffice:default';

    private $postOffice;

    function __construct(Container $container) {
        parent::__construct($container);

        $this->postOffice = $this->container->get(PostOffice::class);
    }

    function actionDefault($id = NULL) {
        $this->template->postOfficeLink = self::LINK_DEFAULT;

        $user = $this->userContext->getEntity();

        $event = NULL;
        if($id === NULL) {
            $event = $this->postOffice->getCurrentParticipatingEvent();
        } else {
            $event = $this->orm->events->getBy(['slug' => $id, 'visible' => TRUE]);
        }

        if($id !== NULL && $event === NULL) {
            throw new BadRequestException;
        }

        $this->template->userEntity = $user;
        $this->template->selectedEvent = $event;
        $this->template->currentEvent = $this->postOffice->getCurrentParticipatingEvent();
        $this->template->events = $this->postOffice->findEventsWithLetters();
        $this->template->letters = $event === NULL ? [] : $user->findLettersAtEvent($event);
        $this->template->directionParentToChild = Letter::DIRECTION_PARENT_TO_CHILD;
        $this->template->directionChildToParent = Letter::DIRECTION_CHILD_TO_PARENT;
        $this->template->showForm = $showForm = ($id === NULL && $event !== NULL);

        // mark read
        if($id === NULL) {
            $this->postOffice->markRead();
            $this->template->countUnreadLetters = 0;
        }

        if($showForm) {
            /** @var Form $f */
            $f = $this->container->get(FormFactory::class)->create();
            $f->setRenderer(new BootstrapVerticalRenderer);
            $f->addTextArea('message', 'Text dopisu')->setRequired();
            $f->addSubmit('send', 'Odeslat dopis');
            $f->onSuccess[] = function(Form $f) {
                if($f->isSubmitted() === $f['send']) {
                    $data = $f->getValues(TRUE);
                    $message = $data['message'];

                    try {
                        $letter = $this->postOffice->sendLetter($message);
                        $this->flashMessage('Váš dopis byl odeslán.', 'success');
                        $this->redirect('this#letter-' . $letter->id);
                    } catch (NoCurrentEventException $e) {
                        $this->flashMessage('Dopis není možné odeslat.', 'danger');
                        $this->redirect('this');
                    }
                }
            };
            $this->addComponent($f, 'form');
        }
    }
    
}
