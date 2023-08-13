<?php

require __DIR__ . '/dev.php';

require __DIR__ . '/../vendor/autoload.php';
$c = require 'container.php';

Tracy\Debugger::enable(
    DEV ? Tracy\Debugger::DEVELOPMENT :Tracy\Debugger::PRODUCTION
    //Tracy\Debugger::DEVELOPMENT
    //['127.0.0.1', 'my-tracy-secret@94.230.144.41']
    ,
    __DIR__ . '/../log'
);

//Tracy\Debugger::enable(Tracy\Debugger::PRODUCTION, __DIR__ . '/../log');

$c->get(\Hafo\PostDeploy\PostDeployScript::class)->run();
