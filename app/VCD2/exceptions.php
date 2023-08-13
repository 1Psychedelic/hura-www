<?php

namespace VCD2;

use Hafo\UI\FlashMessage;
use Nette\Application\UI\Control;

class Exception extends \Exception {}

class FlashMessageException extends Exception {

    private $flashMessage;

    /**
     * @param string $message
     * @param int $code
     * @param \Exception|NULL $previous
     * @param FlashMessage|NULL $flashMessage
     */
    final public function __construct($message = '', $code = 0, \Exception $previous = NULL, FlashMessage $flashMessage = NULL) {
        parent::__construct($message, $code, $previous);

        $this->flashMessage = $flashMessage;
    }

    function getFlashMessage() {
        return $this->flashMessage;
    }

    function flashMessage(Control $control) {
        if($this->flashMessage !== NULL) {
            $control->flashMessage($this->flashMessage);
        }
    }

    /**
     * @param string $message
     * @param FlashMessage|string|NULL $flashMessage
     * @param string $flashMessageType
     * @param bool $dismissable
     * @param null $icon
     * @return static
     */
    static function create($message, $flashMessage = NULL, $flashMessageType = 'danger', $dismissable = TRUE, $icon = NULL) {
        $flashMessage = $flashMessage === NULL || $flashMessage instanceof FlashMessage
            ? $flashMessage
            : new FlashMessage($flashMessageType, $flashMessage, $dismissable, $icon);
        return new static($message, 0, NULL, $flashMessage);
    }

}
