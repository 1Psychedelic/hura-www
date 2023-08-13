<?php

namespace VCD\UI\FrontModule\HomepageModule;

use Hafo\DI\Container;
use Nette\Application\UI\Control;
use VCD2\Carousel\AbstractCarouselPage;
use VCD2\Carousel\Carousel;
use VCD\UI\FrontModule\EventsModule\EventTermControl;
use VCD2\Carousel\CarouselEventPage;
use VCD2\Users\Service\UserContext;

class CarouselControl extends Control {

    private $pages;

    private $startpage;

    private $ride;

    private $container;

    /**
     * @param Container $container
     * @param AbstractCarouselPage[] $pages
     * @param int $startpage
     * @param bool $ride
     */
    function __construct(Container $container, /*AbstractCarouselPage[]*/$pages, $startpage = 0, $ride = TRUE) {
        $this->container = $container;
        $this->pages = $pages;
        $this->startpage = $startpage;
        $this->ride = $ride;
        foreach($pages as $i => $page) {
            if($page instanceof CarouselEventPage) {
                $this->addComponent(new EventTermControl($page->event->starts, $page->event->ends), $i);
            }
        }
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->pages = $this->pages;
        $this->template->startpage = $this->startpage;
        $this->template->ride = $this->ride;
        $this->template->userEntity = $this->container->get(UserContext::class)->getEntity();
        $this->template->isEventPage = function(AbstractCarouselPage $page) {
            return $page instanceof CarouselEventPage;
        };
        $this->template->render();
    }

}
