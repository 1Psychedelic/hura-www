<?php

namespace VCD2\Emails\Service\Emails;

use Nette\Database\Context;
use Nette\Mail\Message;
use VCD2\Emails\Service\Mailer;
use VCD2\Orm;

class PasswordChangeMail {

    private $mailer;

    private $orm;

    function __construct(Mailer $mailer, Orm $orm) {
        $this->mailer = $mailer;
        $this->orm = $orm;
    }

    function send($id) {

        $user = $this->orm->users->get($id);
        if($user === NULL || $user->passwordRestore === NULL) {
            return;
        }

        $message = $this->mailer->createMessage();
        $message->addTo($user->email, $user->name);
        $message->setSubject('Váš odkaz pro obnovení zapomenutého hesla na webu Hurá tábory');

        $template = $this->mailer->createTemplate();
        $template->setFile(__DIR__ . '/default.latte');
        $template->setParameters([
            'hash' => $user->passwordRestore
        ]);

        $this->mailer->send($message, $template);
    }

}
