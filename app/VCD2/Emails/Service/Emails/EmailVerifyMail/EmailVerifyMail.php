<?php

namespace VCD2\Emails\Service\Emails;

use Nette\Database\Context;
use Nette\Mail\Message;
use Nette\Utils\Random;
use VCD2\Emails\Service\Mailer;

class EmailVerifyMail {

    private $mailer;

    function __construct(Mailer $mailer) {
        $this->mailer = $mailer;
    }

    function send($email, $hash) {
        $message = $this->mailer->createMessage();
        $message->addTo($email);
        $message->setSubject('Registrace na webu Hurá tábory - ověření e-mailové adresy');

        $template = $this->mailer->createTemplate();
        $template->setFile(__DIR__ . '/default.latte');
        $template->setParameters([
            'email' => $email,
            'hash' => $hash
        ]);

        $this->mailer->send($message, $template);
    }

}
