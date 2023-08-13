<?php

namespace Hafo\UI;

class FlashMessage {

    private $type;

    private $message;

    private $dismissable;

    private $icon;

    public function __construct($type, $message, $dismissable = TRUE, $icon = NULL) {
        $this->type = $type;
        $this->message = $message;
        $this->dismissable = $dismissable;
        $this->icon = $icon;
    }

    function getType() {
        return $this->type;
    }

    function getMessage() {
        return $this->message;
    }

    function isDismissable() {
        return $this->dismissable;
    }

    function getIcon() {
        return $this->icon;
    }

}
