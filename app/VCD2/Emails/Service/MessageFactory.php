<?php

namespace VCD2\Emails\Service;

use Nette\Mail\Message;

class MessageFactory {

    private $fromEmail;

    private $fromName;

    private $bcc;

    function __construct($fromEmail, $fromName, $bcc = NULL) {
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
        $this->bcc = $bcc;
    }

    /** @return Message */
    function create() {
        $message = new Message;
        $message->setFrom($this->fromEmail, $this->fromName);
        if($this->bcc !== NULL) {
            $message->addBcc($this->bcc);
        }

        return $message;
    }

}
