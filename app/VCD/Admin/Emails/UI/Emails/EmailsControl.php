<?php

namespace VCD\Admin\Emails\UI;

use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Mail\Message;
use Nette\Security\User;
use VCD2\Emails\Service\Mailer;

class EmailsControl extends Control {

    private $container;

    function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    function handleNewsletterSend($id) {
        $mailer = $this->container->get(Mailer::class);
        $email = $this->db()->table('vcd_email')->wherePrimary($id)->fetch();
        $i = 0;
        foreach($this->db()->table('vcd_newsletter')->fetchPairs(NULL, 'email') as $to) {
            $msg = new Message;
            $msg->setSubject($email['name']);
            $msg->setFrom('info@volnycasdeti.cz')->addTo($to);
            foreach($email->related('vcd_email_attachment') as $attachment) {
                $msg->addAttachment($this->container->get('www') . '/' . $attachment['file']);
            }

            $tpl = $mailer->createTemplate();
            $tpl->setParameters(['body' => $email['message']]);
            $mailer->send($msg, $tpl);
            $i++;
        }
        $this->presenter->flashMessage($i . ' e-mailů odesláno.', 'success');
        $this->presenter->redirect('this');
    }

    function handleEmailTest($id) {
        $mailer = $this->container->get(Mailer::class);
        $email = $this->db()->table('vcd_email')->wherePrimary($id)->fetch();
        $msg = new Message;
        $msg->setSubject($email['name']);
        $msg->setFrom('info@volnycasdeti.cz')->addTo($this->container->get(User::class)->identity->data['email']);
        foreach($email->related('vcd_email_attachment') as $attachment) {
            $msg->addAttachment($this->container->get('www') . '/' . $attachment['file']);
        }
        $tpl = $mailer->createTemplate();
        $tpl->setParameters(['body' => $email['message']]);
        $mailer->send($msg, $tpl);
        $this->presenter->flashMessage('Testovací e-mail byl odeslán na adresu ' . $this->container->get(User::class)->identity->data['email'] . '.', 'success');
        $this->presenter->redirect('this');
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $selection = $this->container->get(Context::class)->table('vcd_email');
        $this->template->emails = $selection;
        $this->template->render();
    }

    private function db() {
        return $this->container->get(Context::class);
    }

}
