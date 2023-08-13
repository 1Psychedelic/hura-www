<?php

namespace VCD2\Carousel;
use Nette\Application\LinkGenerator;
use VCD\UI\FrontModule\EventsModule\EventPresenter;
use VCD2\Events\Event;

/**
 * @property Event|NULL $related {m:1 Event, oneSided=TRUE}
 * @property Event|NULL $event {virtual}
 */
class CarouselEventPage extends AbstractCarouselPage {

    const TYPE_PAGE = 0;
    const TYPE_EVENT_PAGE = 1;

    function __construct(Carousel $carousel, $position, Event $event) {
        parent::__construct($carousel, $position);

        $this->related = $event;
    }

    protected function getterContent($data = NULL) {
        return $this->related->description;
    }

    protected function getterLink($data = NULL) {
        return $this->container->get(LinkGenerator::class)->link(substr(EventPresenter::LINK_DEFAULT, 1), ['_event' => $this->related->slug]);
    }

    protected function getterButton($data = NULL) {
        return 'Více informací';
    }

    protected function getterEvent() {
        return $this->related;
    }

}
