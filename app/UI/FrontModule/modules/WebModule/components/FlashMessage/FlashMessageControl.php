<?php

namespace VCD\UI\FrontModule\WebModule;

use Hafo\UI\FlashMessage;
use Nette\Application\UI\Control;

class FlashMessageControl extends Control {

    private $flashes = [];

    private $enabled = TRUE;

    public function __construct($flashes) {
        foreach($flashes as $flash) {
            $this->addFlashMessage(new FlashMessage(
                $flash->type,
                $flash->message
            ));
        }
    }

    public function addFlashMessage(FlashMessage $flashMessage) {
        $this->flashes[] = $flashMessage;
        return $this;
    }

    public function disable() {
        $this->enabled = FALSE;
    }

    public function enable() {
        $this->enabled = TRUE;
    }

    public function isEnabled() {
        return $this->enabled;
    }

    public function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->flashMessages = $this->flashes;
        $this->template->enabled = $this->enabled;
        $this->template->render();
    }

}
