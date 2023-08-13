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
 * @property ManyHasOne|Event $event {m:1 Event::$addons}
 * @property OneHasMany|ApplicationAddon[] $applicationAddons {1:m ApplicationAddon::$addon}
 * @property string $name
 * @property int $price
 * @property bool $enabled
 * @property int $position
 * @property string $description
 * @property string $icon
 * @property string|null $linkUrl
 * @property string|null $linkText
 */
class EventAddon extends Entity
{
    public function __construct(Event $event)
    {
        parent::__construct();
        $this->event = $event;
    }

    public function onBeforePersist()
    {
        $this->event->updatedAt = new DateTimeImmutable();
    }
}
