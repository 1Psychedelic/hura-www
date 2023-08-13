<?php

namespace VCD\Ads\UI;

use Nette\Application\UI\Control;

class AdControl extends Control {

    private $enabled;

    private $url;

    private $image;

    private $classesImg;

    private $classesA;

    function __construct($enabled, $url, $image, $classesImg, $classesA) {
        $this->enabled = $enabled;
        $this->url = $url;
        $this->image = $image;
        $this->classesImg = $classesImg;
        $this->classesA = $classesA;
    }


    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->enabled = $this->enabled;
        $this->template->url = $this->url;
        $this->template->image = $this->image;
        $this->template->classesImg = $this->classesImg;
        $this->template->classesA = $this->classesA;
        $this->template->render();
    }

}
