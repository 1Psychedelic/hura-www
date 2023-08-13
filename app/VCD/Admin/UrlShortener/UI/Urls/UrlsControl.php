<?php

namespace VCD\Admin\UrlShortener\UI;

use Hafo\DI\Container;
use Nette\Application\UI\Control;
use Nette\Database\Context;

class UrlsControl extends Control {

    function __construct(Container $container) {

        $this->onAnchor[] = function () use ($container) {

            $db = $container->get(Context::class);

            $this->template->list = $db->table('vcd_short_url')->order('id DESC');

        };

    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->render();
    }

}
