<?php

use Hafo\DI\Container;

return [
    
    \Hafo\NameDays\NameDays::class => function(Container $c) {
        return new \Hafo\NameDays\NameDays\DatabaseNameDays($c->get(\Nette\Database\Context::class));
    },
    
];
