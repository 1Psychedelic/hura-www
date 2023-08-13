<?php

use Hafo\DI\Container;

return [

    \Hafo\Ares\Ares::class => function (Container $c) {
        return new Hafo\Ares\Ares\Ares(
            new \GuzzleHttp\Client(['verify' => $c->get('ssl.verify')]),
            $c->get(\Monolog\Logger::class)
        );
    },

];
