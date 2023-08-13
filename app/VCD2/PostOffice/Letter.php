<?php

namespace VCD2\PostOffice;

use Hafo\Orm\Entity\Entity;
use Nextras\Orm\Relationships\ManyHasOne;
use VCD2\Events\Event;
use VCD2\Users\User;

/**
 * @property int $id {primary}
 *
 *
 **** Základní údaje
 * @property ManyHasOne|User $user {m:1 User::$letters}
 * @property ManyHasOne|Event $event {m:1 Event, oneSided=TRUE}
 * @property \DateTimeImmutable $createdAt {default now}
 * @property bool $isRead {default FALSE}
 * @property int $direction {enum self::DIRECTION_*}
 * @property bool $visible {default FALSE}
 *
 *
 **** Obsah
 * @property string|NULL $message
 * @property string|NULL $imageUrl
 *
 *
 */
class Letter extends Entity {

    const DIRECTION_CHILD_TO_PARENT = 0;
    const DIRECTION_PARENT_TO_CHILD = 1;

    function __construct(User $user, Event $event, $direction, $message = NULL, $imageUrl = NULL) {
        parent::__construct();

        $this->user = $user;
        $this->event = $event;
        $this->direction = $direction;
        $this->message = $message;
        $this->imageUrl = $imageUrl;

        $this->isRead = FALSE;
        $this->visible = $direction === self::DIRECTION_PARENT_TO_CHILD;
    }

}
