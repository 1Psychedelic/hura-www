<?php

namespace VCD2\Emails\Service\Emails;

use Nette\Database\Context;
use Nette\Mail\Message;
use VCD\UI\FrontModule\ApplicationsModule\ApplicationPresenter;
use VCD2\Emails\Service\Mailer;
use VCD2\Orm;

class ApplicationVerifyMail {

    private $mailer;

    private $orm;

    private $database;

    function __construct(Mailer $mailer, Orm $orm, Context $database) {
        $this->mailer = $mailer;
        $this->orm = $orm;
        $this->database = $database;
    }

    function send($email, $hash, $eventId) {
        $event = $this->orm->events->get($eventId);
        if($event === NULL) {
            return;
        }

        $message = $this->mailer->createMessage();
        $message->addTo($email);
        $message->setSubject("Přihláška na webu Hurá tábory - ověření e-mailové adresy");

        $template = $this->mailer->createTemplate();
        $template->setFile(__DIR__ . '/default.latte');
        $template->setParameters([
            'email' => $email,
            'hash' => $hash,
            'event' => $event,
            'applicationLink' => substr(ApplicationPresenter::LINK_PARENT_INFO, 1),
        ]);

        $this->mailer->send($message, $template);
    }

}
