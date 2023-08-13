<?php

namespace Hafo\Gallery\UI;

use Hafo\DI\Container;
use Hafo\Gallery\Gallery;
use Nette\Application\UI\Control;

class SimpleGalleryControl extends Control {

    private $container;

    function __construct(Container $container, Gallery $gallery) {
        $this->container = $container;

        $this->onAnchor[] = function() use ($gallery) {
            $this->template->gallery = $gallery;
            $this->template->thumbnail = function($w, $h, $desiredHeight = 180) {

                if ($h > $desiredHeight) { // fit height
                    $scale = $desiredHeight / $h;
                } else {
                    $scale = 1;
                }
                $thumbW = (int) round($w * $scale);
                $thumbH = (int) round($h * $scale);

                return 'width:' . floor($thumbW) . 'px;height:' . floor($thumbH) . 'px';
            };
        };
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->render();
    }

}
