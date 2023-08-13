<?php

use Hafo\DI\Container;
use VCD2\Applications\Application;
use VCD2\Applications\Payment;
use VCD2\Applications\Service\Applications;
use VCD2\Applications\Service\Invoices;
use VCD2\Applications\Service\Payments;
use VCD2\Orm;

return [

    Applications::class => function(Applications $applications, Container $c) {

        $applications->onAccept[] = function($id, Application $application) use ($c) {

            // Přihláška přijata - vystavit fakturu zaměstnavateli
            //$c->get(\VCD2\Applications\Service\Invoices::class)->createInvoiceToBePaid($application);

            // Přihláška přijata - notifikace
            $c->get(\VCD\Notifications\Notifications::class)->add(
                'Přihláška #' . $id . ' byla přijata.',
                $c->get(\Nette\Security\User::class)->getId(),
                $id,
                \VCD\Notifications\Notifications::TYPE_APPLICATION
            );

            // Přihláška přijata - e-mail
            $c->get(\VCD2\Emails\Service\Emails\ApplicationAcceptedMail::class)->send($id);
        };

        $applications->onReject[] = function($id, $reason, Application $application) use ($c) {

            // Přihláška odmítnuta - notifikace
            $c->get(\VCD\Notifications\Notifications::class)->add(
                'Přihláška #' . $id . ' byla odmítnuta.',
                $c->get(\Nette\Security\User::class)->getId(),
                $id,
                \VCD\Notifications\Notifications::TYPE_APPLICATION
            );

            // Přihláška odmítnuta - e-mail
            $c->get(\VCD2\Emails\Service\Emails\ApplicationRejectedMail::class)->send($id, $reason);
        };

    },

    Payments::class => function(Payments $payments, Container $c) {

        $payments->onReceivePayment[] = function(Payment $payment) use ($c) {

            // Přijata platba - notifikace
            $c->get(\VCD\Notifications\Notifications::class)->add(
                'Přijata platba ' . $payment->amount . 'Kč za přihlášku #' . $payment->application->id,
                $c->get(\Nette\Security\User::class)->getId(),
                $payment->application->id,
                \VCD\Notifications\Notifications::TYPE_APPLICATION
            );

            $c->get(Orm::class)->refreshAll();

            // Přijata platba - vystavení faktury
            $c->get(Invoices::class)->createInvoice($payment->application);

            // Přijata platba - e-mail
            $c->get(\VCD2\Emails\Service\Emails\ApplicationPaidMail::class)->send($payment->application->id, $payment->amount);
        };
    },

];
