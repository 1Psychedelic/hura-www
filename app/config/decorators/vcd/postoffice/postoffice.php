<?php

use Hafo\DI\Container;
use VCD\Notifications\Notifications;

return [

    \VCD2\PostOffice\Service\PostOffice::class => function(\VCD2\PostOffice\Service\PostOffice $postOffice, Container $c) {

        $postOffice->onSendLetter[] = function(\VCD2\PostOffice\Letter $letter) use ($c) {

            $c->get(\VCD\Notifications\Notifications::class)->add(
                sprintf('Uživatel %s poslal dopis!', $letter->user->name),
                $letter->user->id,
                null,
                Notifications::TYPE_MESSAGE,
                true
            );

        };

    },

];
