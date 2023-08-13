<?php

namespace VCD2\Emails\Service\Emails;

use Nette\Database\Context;
use Nette\Mail\Message;
use VCD2\Applications\Service\InvoiceGenerator;
use VCD2\Emails\Service\Mailer;
use VCD2\Orm;

class ApplicationAcceptedMail {

    private $mailer;

    private $orm;

    private $dir;

    private $database;

    private $invoiceGenerator;

    function __construct(Mailer $mailer, Orm $orm, Context $database, InvoiceGenerator $invoiceGenerator, $dir) {
        $this->mailer = $mailer;
        $this->orm = $orm;
        $this->dir = $dir;
        $this->database = $database;
        $this->invoiceGenerator = $invoiceGenerator;
    }

    function send($id) {
        $application = $this->orm->applications->get($id);
        if($application === NULL) {
            return;
        }

        $message = $this->mailer->createMessage();
        $message->addTo($application->email, $application->name);
        $message->setSubject("Přihláška {$id} byla schválena.");

        // Přílohy nastavené v detailu akce
        if($application->event->acceptedEmail !== NULL) {
            foreach($application->event->acceptedEmail->attachments as $attachment) {
                $message->addAttachment($this->dir . '/' . $attachment->file);
            }
        }

        // Faktura
        if($application->invoice !== NULL) {
            $content = $this->invoiceGenerator->generate($application->invoice);
            $message->addAttachment($application->invoice->invoiceId . '.pdf', $content, 'application/pdf');
        }

        $template = $this->mailer->createTemplate();
        $template->setFile(__DIR__ . '/default.latte');
        $template->setParameters([
            'id' => $id,
            'application' => $application,
            'bankAccount' => $this->database->table('system_website')->fetch()['bank_account'],
            'event' => $application->event->name,
        ]);

        $this->mailer->send($message, $template);
    }

}
