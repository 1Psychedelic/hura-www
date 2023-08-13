<?php

namespace VCD\UI\FrontModule\EventsModule;

use Nette\Application\UI\Control;
use VCD\UI\FrontModule\ApplicationsModule\ApplicationPresenter;
use VCD\UI\FrontModule\UserModule\ProfilePresenter;
use VCD2\Events\Event;
use VCD2\Users\User;

class EventButtonControl extends Control {

    private $event;

    private $user;

    private $style;

    function __construct(Event $event, User $user = NULL, $style = 'default') {
        $this->event = $event;
        $this->user = $user;
        if(!in_array($style, ['default', 'list'])) {
            $this->style = 'default';
        } else {
            $this->style = $style;
        }
    }

    function render() {
        $this->template->setFile(__DIR__ . '/' . $this->style . '.latte');
        $this->template->event = $this->event;
        $this->template->userEntity = $this->user;
        $this->template->hasApplied = $this->user !== NULL && $this->user->hasAppliedForEvent($this->event);
        $this->template->profileLink = ProfilePresenter::LINK_DEFAULT;
        $this->template->draftLink = ApplicationPresenter::LINK_DEFAULT;
        $this->template->finishedLink = \VCD\UI\FrontModule\UserModule\ApplicationPresenter::LINK_DEFAULT;
        $this->template->render();
    }

}
