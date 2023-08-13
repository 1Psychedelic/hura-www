<?php

namespace VCD2\Emails\Service\Emails;

use Nette\Mail\Message;
use VCD\UI\FrontModule\UserModule\PostOfficePresenter;
use VCD2\Emails\Service\Mailer;

class NewLetterMail {

    private $mailer;

    function __construct(Mailer $mailer) {
        $this->mailer = $mailer;
    }

    function send($emails = []) {
        $message = $this->mailer->createMessage();
        $message->setSubject('Přišel Vám pohled z akce!');

        foreach($emails as $email) {
            $message->addBcc($email);
        }

        $template = $this->mailer->createTemplate();
        $template->setFile(__DIR__ . '/default.latte');
        $template->setParameters([
            'postOfficeLink' => substr(PostOfficePresenter::LINK_DEFAULT, 1),
        ]);

        $this->mailer->send($message, $template);
    }

}
