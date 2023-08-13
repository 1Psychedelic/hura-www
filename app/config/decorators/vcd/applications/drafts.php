<?php

use GuzzleHttp\Client;
use Hafo\DI\Container;
use HuraTabory\API\V1\UserProfile\Transformer\ReservationTransformer;
use Monolog\Logger;
use VCD2\Applications\Service\Drafts;

return [

    Drafts::class => function (Drafts $drafts, Container $c) {
        $drafts->onFinish[] = function ($id, $hash, \VCD2\Applications\Application $application) use ($c) {

            // Nová přihláška - notifikace
            $c->get(\VCD\Notifications\Notifications::class)->add(
                'Nová přihláška! #' . $id,
                $application->user === null ? null : $application->user->id,
                $id,
                \VCD\Notifications\Notifications::TYPE_APPLICATION,
                true
            );

            // Nová přihláška - e-mail
            try {
                $c->get(\VCD2\Emails\Service\Emails\ApplicationAppliedMail::class)->send($id);
            } catch (Throwable $e) {
                $c->get(Logger::class)->error($e->getMessage(), ['exception' => $e]);
            }

            // Nová přihláška - Google konverze
            try {
                $c->get(\Hafo\Google\ConversionTracking\Tracker::class)->addConversion('Prodej', 'j9urCMv96oMBEMiF9YkD', $id);
            } catch (Throwable $e) {
                $c->get(Logger::class)->error($e->getMessage(), ['exception' => $e]);
            }

            // Nová přihláška - Facebook Pixel purchase
            try {
                $c->get(\Hafo\Facebook\FacebookPixel\FacebookPixel::class)->addPurchase([
                    'countChildren' => $application->children->count(),
                    'price' => $application->price,
                ]);
            } catch (Throwable $e) {
                $c->get(Logger::class)->error($e->getMessage(), ['exception' => $e]);
            }


            // Nová přihláška - Google Analytics event
            try {
                $c->get(\Hafo\Google\Analytics\Analytics::class)->addEvent(
                    'application',
                    'send',
                    $application->event->id . ' ' . $application->event->name
                );
            } catch (Throwable $e) {
                $c->get(Logger::class)->error($e->getMessage(), ['exception' => $e]);
            }

            // Nová přihláška - integromat
            try {
                (new Client())->request(
                    'POST',
                    'https://hook.integromat.com/ug6ylwxcsb47xckleqe9jg5b52o9bho5',
                    [
                        'json' => $c->get(ReservationTransformer::class)->transform($application),
                    ]
                );
            } catch (Throwable $e) {
                $c->get(Logger::class)->error($e->getMessage(), ['exception' => $e]);
            }
        };
    },

];
