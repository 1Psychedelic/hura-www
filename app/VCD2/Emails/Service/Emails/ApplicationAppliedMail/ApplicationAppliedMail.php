<?php

namespace VCD2\Emails\Service\Emails;

use Nette\Database\Context;
use Nette\Mail\Message;
use VCD2\Emails\Service\Mailer;
use VCD2\Orm;

class ApplicationAppliedMail {

    private $mailer;

    private $orm;

    private $database;

    function __construct(Mailer $mailer, Orm $orm, Context $database) {
        $this->mailer = $mailer;
        $this->orm = $orm;
        $this->database = $database;
    }

    function send($id) {
        $application = $this->orm->applications->get($id);
        if($application === NULL) {
            return;
        }

        $message = $this->mailer->createMessage();
        $message->addTo($application->email, $application->name);
        $message->setSubject('Hurá, přihláška úspěšně odeslána!');

        $template = $this->mailer->createTemplate();
        $template->setFile(__DIR__ . '/default.latte');
        $template->setParameters([
            'id' => $id,
            'event' => $application->event->name,
            'application' => $application,
            'bankAccount' => $this->database->table('system_website')->fetch()['bank_account'],
        ]);

        $this->mailer->send($message, $template);
    }

}
