<?php
declare(strict_types=1);

namespace VCD2\Events;

use DateTimeImmutable;
use Nextras\Orm\Relationships\ManyHasOne;
use Nextras\Orm\Relationships\OneHasMany;
use VCD2\Applications\ApplicationAddon;
use VCD2\Entity;

/**
 * @property int $id {primary}
 * @property ManyHasOne|Event $event {m:1 Event::$images}
 * @property string $name
 * @property int $position
 * @property int $thumbW
 * @property int $thumbH
 */
class EventImage extends Entity
{
    public function __construct(Event $event)
    {
        parent::__construct();
        $this->event = $event;
    }
}
