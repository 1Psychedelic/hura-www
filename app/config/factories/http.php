<?php

use Hafo\DI\Container;

return [
    \Psr\Http\Message\ServerRequestInterface::class => function (Container $container) {
        return \Zend\Diactoros\ServerRequestFactory::fromGlobals(
            $_SERVER,
            $_GET,
            $_POST,
            $_COOKIE,
            $_FILES
        );
    },

    \Zend\HttpHandlerRunner\Emitter\EmitterInterface::class => function (Container $container) {
        return new \Zend\HttpHandlerRunner\Emitter\SapiEmitter();
    },
];
