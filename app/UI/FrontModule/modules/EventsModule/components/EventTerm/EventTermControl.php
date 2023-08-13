<?php

namespace VCD\UI\FrontModule\EventsModule;

use Nette\Application\UI\Control;

class EventTermControl extends Control {

    private $starts;

    private $ends;

    private $monthAsNumber;

    function __construct(\DateTimeInterface $starts, \DateTimeInterface $ends, $monthAsNumber = TRUE) {
        $this->starts = $starts;
        $this->ends = $ends;
        $this->monthAsNumber = $monthAsNumber;
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->starts = $this->starts;
        $this->template->ends = $this->ends;
        $this->template->monthAsNumber = $this->monthAsNumber;
        $this->template->render();
    }

}
