<?php

namespace VCD2\Emails\Service\Emails;

use HuraTabory\Domain\Website\WebsiteRepository;
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;
use VCD2\Emails\Service\Mailer;

class ContactFormMail {

    /** @var Mailer */
    private $mailer;

    /** @var SendmailMailer */
    private $sendmailMailer;

    /** @var WebsiteRepository */
    private $websiteRepository;

    function __construct(Mailer $mailer, SendmailMailer $sendmailMailer, WebsiteRepository $websiteRepository) {
        $this->mailer = $mailer;
        $this->sendmailMailer = $sendmailMailer;
        $this->websiteRepository = $websiteRepository;
    }

    function send($name, $email, $subject, $message) {

        $adminEmail = $this->websiteRepository->getWebsiteConfig()->getEmail();

        $params = [
            'name' => $name,
            'email' => $email,
            'message' => str_replace("\n", '</p><p>', strip_tags($message)),
            'subject' => $subject,
        ];

        $message = $this->mailer->createMessage();
        $message->setFrom($email, $name)->addTo($adminEmail);
        $message->setSubject($subject . ' (zpráva z kontaktního formuláře)');

        $template = $this->mailer->createTemplate();
        $template->setFile(__DIR__ . '/admin.latte');
        $template->setParameters($params);

        $this->mailer->send($message, $template, $this->sendmailMailer); // sendmail, protože gmail přepisuje odesílatele

        $message = $this->mailer->createMessage();
        $message->addTo($email, $name);
        $message->setSubject('Vaše zpráva z kontaktního formuláře Hurá tábory');

        $template = $this->mailer->createTemplate();
        $template->setFile(__DIR__ . '/user.latte');
        $template->setParameters($params);

        $this->mailer->send($message, $template);
    }

}
