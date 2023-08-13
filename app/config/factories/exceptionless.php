<?php

use Hafo\DI\Container;

return [
    \Hafo\Exceptionless\Client::class => function (Container $c) {
        return new \Hafo\Exceptionless\Client(
            'https://exceptionless.lukasklika.cz',
            'EiOENSv22B5dyWfvxqXGaOpAL8TMAoS9AMmU6k36',
            $c->get(\Nette\Http\IRequest::class)
        );
    }
];
