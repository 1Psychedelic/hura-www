<?php

use Hafo\DI\Container;
use HuraTabory\Domain\Website\WebsiteRepository;

return [

    \VCD2\Emails\Service\Mailer::class => function(Container $c) {
        $presenter = $c->get(\Nette\Application\IPresenterFactory::class)->createPresenter('Front:Homepage:Homepage');
        $basePath = $presenter->template->basePath;
        $baseUri = $presenter->template->baseUri;
        return new \VCD2\Emails\Service\Mailer(
            $c->get(Nette\Mail\IMailer::class),
            $c->get(\VCD2\Emails\Service\MessageFactory::class),
            $c->get(\Nette\Application\UI\ITemplateFactory::class),
            $c->get(\Nette\Application\LinkGenerator::class),
            $c->get(WebsiteRepository::class),
            $basePath,
            $baseUri
        );
    },

    \VCD2\Emails\Service\Emails\ApplicationAcceptedMail::class => function(Container $c) {
        return new \VCD2\Emails\Service\Emails\ApplicationAcceptedMail(
            $c->get(\VCD2\Emails\Service\Mailer::class),
            $c->get(\VCD2\Orm::class),
            $c->get(\Nette\Database\Context::class),
            $c->get(\VCD2\Applications\Service\InvoiceGenerator::class),
            $c->get('www')
        );
    },

    \VCD2\Emails\Service\MessageFactory::class => function(Container $c) {
        $config = $c->get('email.config');
        return new \VCD2\Emails\Service\MessageFactory($config['from_email'], $config['from_name'], $config['bcc_to']);
    },

    Nette\Mail\IMailer::class => function(Container $c) {
        $config = $c->get('email.config');
        $className = \VCD\Admin\Website\UI\EmailConfigControl::TYPES[$config['type']];
        return $c->get($className);
    },

    \Nextras\MailPanel\FileMailer::class => function(Container $c) {
        $mailer = new \Nextras\MailPanel\FileMailer($c->get('tmp'));
        \Tracy\Debugger::getBar()->addPanel(new \Nextras\MailPanel\MailPanel(
            $c->get('tmp'),
            $c->get(\Nette\Http\Request::class),
            $mailer
        ));
        return $mailer;
    },

    \Nette\Mail\SendmailMailer::class => function(Container $c) {
        return new \Nette\Mail\SendmailMailer();
    },

    \Nette\Mail\SmtpMailer::class => function(Container $c) {
        $keys = ['host', 'port', 'secure', 'username', 'password'];
        return new \Nette\Mail\SmtpMailer(array_intersect_key($c->get('email.config'), array_fill_keys($keys, NULL)));
    },

    'email.config' => function(Container $c) {
        $cache = $c->get(\Nette\Caching\Cache::class)->derive('email_config');
        return $cache->load('email_config', function(&$dependencies) use ($c) {
            $dependencies[\Nette\Caching\Cache::TAGS] = ['email_config'];
            return $c->get(\Nette\Database\Context::class)->table('email_config')->fetch()->toArray();
        });
    },

    // todo remove me
    'smtp.config' => function(Container $c) {
        return [
            'host' => 'smtp-136790.m90.wedos.net',
            'port' => 465,
            'secure' => 'ssl',
            'username' => 'test@lukasklika.cz',
            'password' => 'Ax5b8F9?x5',
        ];
    },

];
