<?php

namespace VCD\UI\FrontModule\HomepageModule;

class HomepagePresenter extends BasePresenter {

    const LINK_DEFAULT = ':Front:Homepage:Homepage:default';

    const CAROUSEL_NAME = 'homepage';

    function actionDefault() {

    }

    protected function createComponentCarousel() {
        $carousel = $this->orm->carousels->get(self::CAROUSEL_NAME);
        return new CarouselControl($this->container, $carousel->visiblePages);
    }

}
