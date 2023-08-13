<?php

require __DIR__ . '/dev.php';

$root = __DIR__ . '/../';
require $root . 'vendor/autoload.php';

/*Tracy\Debugger::enable(
    DEV ? Tracy\Debugger::DEVELOPMENT :Tracy\Debugger::PRODUCTION
    //Tracy\Debugger::DEVELOPMENT
    //['127.0.0.1', 'my-tracy-secret@94.230.144.41']
    ,
    $root . 'log'
);*/
//Tracy\Debugger::$strictMode = TRUE;

$c = require 'container.php';

try {
    $c->get(\HuraTabory\API\FrontController::class)->run();
} catch (Throwable $e) {
    if (DEV) {
        throw $e;
    } else {
        $c->get(\Hafo\Exceptionless\Client::class)->logException($e);
    }
}
