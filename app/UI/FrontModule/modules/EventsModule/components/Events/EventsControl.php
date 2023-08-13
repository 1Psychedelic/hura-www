<?php
declare(strict_types=1);

namespace VCD\UI\FrontModule\EventsModule;

use Nextras\Orm\Collection\ICollection;
use VCD\UI\BaseControl;
use VCD2\Events\Event;

class EventsControl extends BaseControl
{
    /**
     * @param Event[]|ICollection $events
     */
    public function __construct($events)
    {
        $this->onAnchor[] = function () use ($events) {
            foreach ($events as $event) {
                $this->addComponent(new EventTermControl($event->starts, $event->ends, false), 'term_' . $event->id);
                if (!$event->isArchived) {
                    $this->addComponent(new EventCapacityControl($event->capacity, $event->countFreeSlots()), 'capacity_' . $event->id);
                }
            }
            $this->template->events = $events;
        };
    }

    public function render()
    {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->render();
    }
}
