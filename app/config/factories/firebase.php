<?php
declare(strict_types=1);

use Hafo\DI\Container;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging;

return [
    Factory::class => function (Container $c) {
        $factory = new Factory();

        $credentialsFile = __DIR__ . '/../local/firebase.json';
        if (file_exists($credentialsFile)) {
            $factory = $factory->withServiceAccount($credentialsFile);
        }

        return $factory;
    },
];
