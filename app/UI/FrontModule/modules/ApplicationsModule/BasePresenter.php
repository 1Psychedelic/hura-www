<?php

namespace VCD\UI\FrontModule\ApplicationsModule;

use Nette\Application\BadRequestException;
use Nette\Utils\Arrays;
use VCD2\Applications\Application;
use VCD2\Applications\Service\Drafts;
use VCD2\Events\Event;

abstract class BasePresenter extends \VCD\UI\FrontModule\BasePresenter {

    /** @persistent */
    public $_event;

    /** @var Event */
    protected $event;

    /** @var Application */
    protected $draft;

    function startup() {
        parent::startup();

        $this->event = $this->orm->events->getBy(['slug' => $this->_event, 'visible' => TRUE]);
        if($this->event === NULL || !$this->event->areApplicationsOpenForUser($this->userContext->getEntity())) {
            throw new BadRequestException;
        }

        $this->draft = $this->container->get(Drafts::class)->openDraftForEvent($this->event);
        $invalidSteps = [];
        foreach($this->draft->invalidStepChoices as $invalidStepChoice) {
            $invalidSteps[] = $invalidStepChoice->step;
        }
        $this->template->invalidSteps = $invalidSteps;
        $this->template->event = $this->event;
        $this->template->draft = $this->draft;

        $this->template->setFile(__DIR__ . '/templates/Application/application.latte');
    }

}
