<?php

namespace Hafo\Orm\Repository;

use Hafo\DI\Container;
use Hafo\Orm\Entity\Entity;
use Nextras\Orm\Entity\IEntity;
use Nextras\Orm\Repository\IDependencyProvider;

class DependencyProvider implements IDependencyProvider {

    private $container;

    function __construct(Container $container) {
        $this->container = $container;
    }

    public function injectDependencies(IEntity $entity)
    {
        if($entity instanceof Entity) {
            $entity->injectContainer($this->container);
        }
    }

}
