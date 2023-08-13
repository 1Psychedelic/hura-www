<?php
declare(strict_types=1);

namespace VCD\UI\FrontModule\EventsModule;

use Nette\Application\UI\Control;

class EventCapacityControl extends Control
{
    /** @var float */
    private $capacity;

    /** @var int */
    private $freeSlots;

    public function __construct(float $capacity, int $freeSlots)
    {
        $this->capacity = $capacity;
        $this->freeSlots = $freeSlots;
    }

    public function render()
    {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->capacity = $this->capacity;
        $this->template->freeSlots = $this->freeSlots;
        $this->template->render();
    }
}
