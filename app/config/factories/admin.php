<?php

use Hafo\DI\Container;

return [

    \VCD\Admin\Applications\NewApplications::class => function(Container $c) {
        return new \VCD\Admin\Applications\DefaultModel\NewApplications($c->get(\Nette\Database\Context::class));
    },

];
