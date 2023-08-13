<?php

namespace VCD2\Users\Repository;

use Hafo\Orm\Repository\Repository;
use Nette\Utils\Strings;
use Nextras\Orm\Collection\ArrayCollection;
use Nextras\Orm\Collection\ICollection;
use VCD2\Users\Child;
use VCD2\Users\Mapper\ChildMapper;

/**
 * @method ChildMapper getMapper()
 *
 * @method Child|NULL get($primaryKey)
 * @method Child|NULL getBy(array $conds)
 *
 * @method Child[]|ICollection find($ids)
 * @method Child[]|ICollection findAll()
 * @method Child[]|ICollection findBy(array $where)
 *
 * @method Child hydrateEntity(array $data)
 */
class ChildRepository extends Repository {

    static function getEntityClassNames() : array {
        return [Child::class];
    }

    function search($q = NULL) {
        $result = [];
        $all = $this->findAll();

        if($q === NULL) {
            return $all;
        }

        foreach($all as $entity) {
            if(Strings::contains($entity->name, $q)) {
                $result[] = $entity;
            }
        }
        return new ArrayCollection($result, $this);
    }

}
