<?php

namespace VCD2\Gallery;
use Nextras\Orm\Relationships\ManyHasOne;
use VCD2\Entity;
use VCD2\Events\Event;

/**
 * @property int $id {primary}
 *
 *
 **** Základní údaje
 * @property Event|ManyHasOne $event {m:1 Event::$allPhotos}
 * @property string $name
 * @property int $position
 * @property bool $visible
 * @property int $type {enum self::TYPE_*}
 *
 *
 **** Náhled
 * @property int $thumbW
 * @property int $thumbH
 *
 * 
 */
abstract class AbstractPhoto extends Entity {

    const TYPE_PHOTO = 0;
    const TYPE_LOST_FOUND = 1;

    /** @internal */
    const INHERITANCE_MAPPING = [
        self::TYPE_PHOTO => Photo::class,
        self::TYPE_LOST_FOUND => LostFound::class,
    ];

    function __construct(Event $event, $name, $position, $visible, $thumbW, $thumbH) {
        parent::__construct();

        $this->event = $event;
        $this->name = $name;
        $this->position = $position;
        $this->visible = $visible;
        $this->thumbW = $thumbW;
        $this->thumbH = $thumbH;
    }

}
