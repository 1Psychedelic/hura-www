<?php

namespace VCD\UI\FrontModule\WebModule;

use Nette\Database\Context;
use VCD2\Events\Event;

class SitemapPresenter extends BasePresenter {

    const LINK_DEFAULT = ':Front:Web:Sitemap:default';

    function actionDefault() {
        $db = $this->container->get(Context::class);
        $this->template->events = $db->table('vcd_event')->where('visible = 1')->order('ends DESC');
        $this->template->typeCamp = Event::TYPE_CAMP;
        $this->template->typeTrip = Event::TYPE_TRIP;
        $this->setLayout(FALSE);
    }

}
