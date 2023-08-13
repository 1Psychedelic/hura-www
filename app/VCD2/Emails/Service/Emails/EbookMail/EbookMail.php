<?php

namespace VCD2\Emails\Service\Emails;

use Nette\Mail\Message;
use VCD\UI\FrontModule\WebModule\EbooksPresenter;
use VCD2\Ebooks\EbookDownloadLink;
use VCD2\Emails\Service\Mailer;

class EbookMail {

    private $mailer;

    function __construct(Mailer $mailer) {
        $this->mailer = $mailer;
    }

    function send($email, $hash) {
        $message = $this->mailer->createMessage();
        $message->addTo($email);
        $message->setSubject('Váš odkaz pro stažení e-booku');

        $template = $this->mailer->createTemplate();
        $template->setFile(__DIR__ . '/default.latte');
        $template->setParameters([
            'email' => $email,
            'hash' => $hash,
            'expirationText' => EbookDownloadLink::EXPIRATION_TEXT,
            'downloadLink' => substr(EbooksPresenter::LINK_DOWNLOAD, 1),
        ]);

        $this->mailer->send($message, $template);
    }

}
