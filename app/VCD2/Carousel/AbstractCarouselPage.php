<?php

namespace VCD2\Carousel;

use VCD2\Entity;

/**
 * @property int $id {primary}
 *
 *
 **** Základní údaje
 * @property Carousel $carousel {m:1 Carousel::$pages}
 * @property int $type {enum self::TYPE_*}
 * @property int $position
 *
 *
 **** Příznaky
 * @property bool $visible {default FALSE}
 *
 *
 **** Obsah a vzhled
 * @property string|NULL $link
 * @property string|NULL $button
 * @property string|NULL $content
 * @property string|NULL $backgroundImage
 * @property string|NULL $backgroundPosition
 * @property int|NULL $boxWidth
 * @property int|NULL $boxTop
 *
 *
 */
abstract class AbstractCarouselPage extends Entity {

    const TYPE_PAGE = 0;
    const TYPE_EVENT_PAGE = 1;

    /** @internal */
    const INHERITANCE_MAPPING = [
        self::TYPE_PAGE => CarouselPage::class,
        self::TYPE_EVENT_PAGE => CarouselEventPage::class,
    ];

    function __construct(Carousel $carousel, $position) {
        parent::__construct();

        $this->carousel = $carousel;
        $this->position = $position;
    }

}
