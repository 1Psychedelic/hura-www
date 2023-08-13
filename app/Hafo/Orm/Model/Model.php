<?php

namespace Hafo\Orm\Model;

use Nextras\Orm\Repository\IRepository;

abstract class Model extends \Nextras\Orm\Model\Model {

    public function getRepository(string $className) : IRepository {
        $repo = parent::getRepository($className);
        $repo->setModel($this);
        return $repo;
    }

}
