<?php

namespace VCD\UI\FrontModule\EventsModule;


class ArchivePresenter extends BasePresenter {

    const LINK_DEFAULT = ':Front:Events:Archive:default';

    function actionDefault() {
        $events = $this->orm->events->findArchived();
        $this->addComponent(new EventsControl($events), 'events');
        $this->template->titlePrefix = 'Archiv';
    }

}
