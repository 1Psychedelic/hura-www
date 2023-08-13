<?php

use Hafo\DI\Container;

return [

    \GoPay\Payments::class =>function (Container $c) {
        $config = [
            'isProductionMode' => FALSE,
            'language' => \GoPay\Definition\Language::CZECH,
            'clientId' => '',
            'clientSecret' => '',
            'goid' => '',
        ];
        return \GoPay\Api::payments($config, [
            'logger' => $c->get(\Monolog\Logger::class),
        ]);
    },

    \Hafo\GoPay\Service\GoPay::class => function(Container $c) {
        return $c->get(\Hafo\GoPay\Service\GoPay\FakeGoPay::class);
    },

    \Hafo\GoPay\Service\GoPay\GoPay::class => function(Container $c) {
        return new Hafo\GoPay\Service\GoPay\GoPay($c->get(\GoPay\Payments::class));
    },

    \Hafo\GoPay\Service\GoPay\FakeGoPay::class => function(Container $c) {
        return new \Hafo\GoPay\Service\GoPay\FakeGoPay(
            $c->get(\Nette\Http\Session::class),
            $c->get(\Nette\Application\LinkGenerator::class)
        );
    },

];
