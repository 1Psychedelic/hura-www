<?php

namespace VCD\UI;

use Nette\Application\UI\Control;
use VCD\UI\FrontModule\WebModule\FlashMessageControl;

/**
 * @property-read BasePresenter $presenter
 */
abstract class BaseControl extends Control {

    protected function createComponentFlashes() {
        return new FlashMessageControl($this->template->flashes);
    }

}
