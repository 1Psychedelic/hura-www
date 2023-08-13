<?php

use Hafo\DI\Container;

return [

    'ssl.verify' => function(Container $c) {
        return FALSE;
    },

];
