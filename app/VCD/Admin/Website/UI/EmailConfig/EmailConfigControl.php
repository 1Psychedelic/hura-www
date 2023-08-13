<?php

namespace VCD\Admin\Website\UI;

use Hafo\DI\Container;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Caching\Cache;
use Nette\Database\Context;
use Nette\Mail\SendmailMailer;
use Nette\Mail\SmtpMailer;
use Nextras\MailPanel\FileMailer;
use VCD2\UI\Admin\Forms\AdminFormRenderer;

class EmailConfigControl extends Control {

    const TYPES = [
        0 => SmtpMailer::class,
        1 => SendmailMailer::class,
        2 => FileMailer::class,
    ];

    const TYPE_NAMES = [
        0 => 'přes SMTP (výchozí)',
        1 => 'přes Sendmail (nespolehlivé)',
        2 => 'do souboru (pro testování)',
    ];

    const SECURE_NAMES = [
        NULL => 'Žádné',
        'ssl' => 'SSL',
        'tls' => 'TLS',
    ];

    private $container;

    function __construct(Container $container) {
        $this->container = $container;

        $this->onAnchor[] = function() {
            $row = $this->db()->table('email_config')->fetch();
            $f = new Form;
            $f->setRenderer(new AdminFormRenderer);

            $f->addGroup('Základní nastavení');
            $f->addText('from_email', 'E-mail odesílatele')->setRequired()->addRule(Form::EMAIL);
            $f->addText('from_name', 'Jméno odesílatele')->setRequired();
            $f->addText('bcc_to', 'Odesílat kopii na')->setNullable()
                ->setAttribute('placeholder', 'Neodesílat kopii')
                ->addCondition(Form::FILLED)
                    ->addRule(Form::EMAIL);

            $f->addRadioList('type', 'Odesílat e-maily', self::TYPE_NAMES);

            $f->addGroup('Nastavení SMTP')->setOption('id', 'email-config-smtp');
            $f->addText('host', 'Host')->setNullable();
            $f->addText('port', 'Port')->setNullable();
            $f->addText('username', 'Přihlašovací jméno')->setNullable();
            $f->addText('password', 'Heslo')->setNullable();
            $f->addRadioList('secure', 'Zabezpečení', self::SECURE_NAMES);

            foreach(['host', 'port', 'username', 'password'] as $field) {
                $f[$field]->addConditionOn($f['type'], Form::EQUAL, 0)
                    ->setRequired();
            }
            $f['type']->addCondition(Form::EQUAL, 0)
                ->toggle('email-config-smtp');

            $f->setCurrentGroup(NULL);
            $f->addSubmit('save', 'Uložit');
            $f->onSuccess[] = function(Form $f) {
                if($f->isSubmitted() === $f['save']) {
                    $this->db()->table('email_config')->update($f->getValues(TRUE));
                    $this->container->get(Cache::class)->clean([Cache::TAGS => ['email_config']]);
                    $this->presenter->flashMessage('Uloženo.', 'success');
                    $this->presenter->redirect('this');
                }
            };
            $f->setValues($row);
            $this->addComponent($f, 'form');
        };
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->render();
    }

    private function db() {
        return $this->container->get(Context::class);
    }

}
