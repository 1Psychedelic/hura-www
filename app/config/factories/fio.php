<?php

use Hafo\DI\Container;

return [

    'fio.token' => function(Container $c) {
        $cache = $c->get(\Nette\Caching\Cache::class)->derive('fio_config');
        return $cache->load('token', function(&$dependencies) use ($c) {
            $dependencies[\Nette\Caching\Cache::TAGS] = ['fio_config'];
            return $c->get(\Nette\Database\Context::class)->table('fio_config')->fetch()['token'];
        });
    },

    \Hafo\Fio\Service\Fio::class => function(Container $c) {
        if($c->get('fio.token') === NULL) {
            return $c->get(\Hafo\Fio\Service\Fio\NoFio::class);
        }
        return $c->get(\Hafo\Fio\Service\Fio\Fio::class);
    },

    \Hafo\Fio\Service\Fio\Fio::class => function(Container $c) {
        return new Hafo\Fio\Service\Fio\Fio(
            $c->get('fio.token'),
            $c->get(\VCD2\Orm::class)->getRepository(\Hafo\Fio\Repository\PaymentRepository::class),
            $c->get(\Monolog\Logger::class),
            new \GuzzleHttp\Client(['verify' => $c->get('ssl.verify')])
        );
    },

    \Hafo\Fio\Service\Fio\NoFio::class => function(Container $c) {
        return new \Hafo\Fio\Service\Fio\NoFio;
    },


];
