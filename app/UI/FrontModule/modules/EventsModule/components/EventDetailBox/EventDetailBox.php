<?php
declare(strict_types=1);

namespace VCD\UI\FrontModule\EventsModule;

use Nette\Application\UI\Control;
use VCD2\Events\Event;
use VCD2\Users\User;

class EventDetailBox extends Control
{
    private $event;

    private $user;

    public function __construct(Event $event, User $user = null)
    {
        $this->event = $event;
        $this->user = $user;
        $this->addComponent(new EventButtonControl($event, $user), 'button');
        $this->addComponent(new EventTermControl($event->starts, $event->ends), 'term');
        $this->addComponent(new EventCapacityControl($event->capacity, $event->countFreeSlots()), 'capacity');
    }

    public function render()
    {
        $this->template->setFile(__DIR__ . '/box.latte');
        $this->template->event = $this->event;
        $this->template->userEntity = $this->user;
        $this->template->addFilter('discounted_until', function (\DateTimeInterface $until) {
            $diff = (new \DateTime)->diff($until);
            if ($diff->invert) {
                return '00:00';
            }
            $r = '';
            if ($diff->days > 0) {
                $r = $diff->days . ' ';
                if ($diff->days === 1) {
                    $r .= 'den';
                } elseif ($diff->days > 1 && $diff->days < 5) {
                    $r .= 'dny';
                } else {
                    $r .= 'dní';
                }

                return $r;
            }
            $r .= (strlen((string)$diff->h) === 1 ? '0' : '') . $diff->h . ':';
            $r .= (strlen((string)$diff->i) === 1 ? '0' : '') . $diff->i . '';

            return $r;
        });
        $this->template->render();
    }
}
