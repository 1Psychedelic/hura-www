<?php


use Hafo\Logger\ExceptionLogger;
use HuraTabory\Web\FrontController;
use Monolog\Logger;
use Nette\Application\Application;

require __DIR__ . '/dev.php';

$root = __DIR__ . '/../';
require $root . 'vendor/autoload.php';

Tracy\Debugger::enable(
    DEV ? Tracy\Debugger::DEVELOPMENT :Tracy\Debugger::PRODUCTION
    //Tracy\Debugger::DEVELOPMENT
    //['127.0.0.1', 'my-tracy-secret@94.230.144.41']
    ,
    $root . 'log'
);
//Tracy\Debugger::$strictMode = TRUE;

$c = require 'container.php';

if(php_sapi_name() === 'cli') {

    //include 'cli.php';

} else {
    try {
        $application = $c->get(Application::class);

        $application->onError[] = static function (Application $application, $e) use ($c) {
            if ($e instanceof Throwable) {
                $logger = $c->get(Logger::class)->withName('vcd.error');
                (new ExceptionLogger($logger))->log($e);
                $c->get(\Hafo\Exceptionless\Client::class)->logException($e);
            }
        };

        $application->run();
    } catch (Throwable $e) {
        if (DEV) {
            throw $e;
        }

        $c->get(\Hafo\Exceptionless\Client::class)->logException($e);
    }
}
