<?php

namespace Hafo\Orm\Repository;

use Nextras\Orm\Entity\IEntity;

/**
 * @method IEntity[] find($ids)
 */
abstract class Repository extends \Nextras\Orm\Repository\Repository {

    public function get($id) {
        return $this->getById($id);
    }

}
