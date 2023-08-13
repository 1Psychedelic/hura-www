<?php

namespace VCD2\Emails\Service\Emails;

use Nette\Mail\Message;
use VCD\UI\AuthModule\CompleteSignupPresenter;
use VCD2\Emails\Service\Mailer;
use VCD2\Orm;

class AccountCreatedMail {

    private $mailer;

    private $orm;

    function __construct(Mailer $mailer, Orm $orm) {
        $this->mailer = $mailer;
        $this->orm = $orm;
    }

    function send($email, $isNew = TRUE) {
        $user = $this->orm->users->getByEmail($email);
        if($user === NULL || $user->canLogin) {
            return;
        }

        $message = $this->mailer->createMessage();
        $message->addTo($user->email, $user->name);
        $message->setSubject('Váš účet na webu Hurá tábory');

        $template = $this->mailer->createTemplate();
        $template->setFile(__DIR__ . '/default.latte');
        $template->setParameters([
            'hash' => $user->loginHash,
            'isNew' => $isNew,
            'completeSignupLink' => substr(CompleteSignupPresenter::LINK_DEFAULT, 1),
        ]);

        $this->mailer->send($message, $template);
    }

}
