<?php

use Hafo\DI\Container;
use Nette\Http\Session;

return [

    Session::class => function(Session $session, Container $c) {
        $session->setExpiration('+30 days');
        $session->start();
    }

];
