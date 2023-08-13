<?php

namespace Hafo\NetteBridge\Latte\LatteFactory;

use Latte\Engine;
use Hafo\NetteBridge\Latte;
use Hafo\DI\Container;

final class ContainerLatteFactory implements Latte\LatteFactory {

    private $container;

    function __construct(Container $container) {
        $this->container = $container;
    }

    function create() {
        return $this->container->create(Engine::class);
    }

}
