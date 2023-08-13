<?php

namespace VCD2\PostOffice\Service;

use Nette\SmartObject;
use Nextras\Dbal\Connection;
use VCD2\Events\Event;
use VCD2\Orm;
use VCD2\PostOffice\Letter;
use VCD2\PostOffice\NoCurrentEventException;
use VCD2\Users\Service\UserContext;
use VCD2\Users\User;

/**
 * @method onSendLetter(Letter $letter)
 */
class PostOffice {

    use SmartObject;

    public $onSendLetter = [];

    private $orm;

    private $user;

    private $connection;

    function __construct(Orm $orm, UserContext $userContext, Connection $connection) {
        $this->orm = $orm;
        $this->user = $userContext->getEntity();
        $this->connection = $connection;
    }

    /**
     * @return Event|NULL
     */
    function getCurrentParticipatingEvent() {
        foreach($this->user->acceptedApplications as $application) {
            if($application->event->isNow) {
                return $application->event;
            }
        }
        return NULL;
    }

    /**
     * @param string $message
     * @throws NoCurrentEventException
     * @return Letter
     */
    function sendLetter($message) {
        $event = $this->getCurrentParticipatingEvent();
        if($event === NULL) {
            throw new NoCurrentEventException;
        }

        $letter = new Letter($this->user, $event, Letter::DIRECTION_PARENT_TO_CHILD, $message);
        $this->orm->persistAndFlush($letter);

        $this->onSendLetter($letter);

        return $letter;
    }

    function markRead() {
        $this->connection->query(
            'UPDATE vcd_letter SET is_read = 1 WHERE user = %i AND visible = 1 AND direction = %i',
            $this->user->id,
            Letter::DIRECTION_CHILD_TO_PARENT
        );
    }

    function countUnreadLetters() {
        return $this->connection->query(
            'SELECT COUNT(id) FROM vcd_letter WHERE is_read = 0 AND visible = 1 AND user = %i AND direction = %i',
            $this->user->id,
            Letter::DIRECTION_CHILD_TO_PARENT
        )->fetchField();
    }

    function findEventsWithLetters() {
        $events = [];
        foreach($this->user->visibleLetters as $letter) {
            $events[$letter->event->id] = $letter->event;
        }
        usort($events, function(Event $a, Event $b) {
            return $b->ends <=> $a->ends;
        });
        return $events;
    }

}
