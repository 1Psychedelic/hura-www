<?php

namespace VCD2\Carousel;

use Nextras\Orm\Relationships\OneHasMany;
use VCD2\Entity;

/**
 * @property string $id {primary}
 *
 *
 **** Základní údaje
 * @property AbstractCarouselPage[]|OneHasMany $pages {1:m CarouselPage::$carousel, orderBy=position}
 * @property AbstractCarouselPage[] $visiblePages {virtual}
 *
 * 
 */
class Carousel extends Entity {

    protected function getterVisiblePages() {
        $pages = [];
        foreach($this->pages->get()->findBy(['visible' => TRUE]) as $page) {
            if($page instanceof CarouselEventPage) {
                if($page->event->hasEnded) {
                    continue;
                }
            }

            $pages[] = $page;
        }
        return $pages;
    }

}
