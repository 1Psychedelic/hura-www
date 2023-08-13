<?php

namespace Hafo\Cron;

use Hafo\Cron\CronRunner\DefaultCronRunner;
use Hafo\DI\Container;
use Hafo\DI\ContainerBuilder;
use Hafo\DI\Module;

final class CronModule implements Module {

    function install(ContainerBuilder $builder) {
        $builder->addFactories([
            CronRunner::class => function(Container $c) {
                return $c->get(DefaultCronRunner::class);
            },

            DefaultCronRunner::class => function(Container $c) {
                return new DefaultCronRunner($c->get(\Nette\Database\Context::class), $c);
            },
        ]);
    }


}
