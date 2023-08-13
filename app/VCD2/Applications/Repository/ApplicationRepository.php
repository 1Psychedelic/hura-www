<?php

namespace VCD2\Applications\Repository;

use Hafo\Orm\Repository\Repository;
use Nextras\Orm\Collection\ICollection;
use VCD2\Applications\Application;
use VCD2\Applications\Mapper\ApplicationMapper;

/**
 * @method ApplicationMapper getMapper()
 *
 * @method Application|NULL get($primaryKey)
 * @method Application|NULL getBy(array $conds)
 *
 * @method Application[]|ICollection find($ids)
 * @method Application[]|ICollection findAll()
 * @method Application[]|ICollection findBy(array $where)
 *
 * @method Application hydrateEntity(array $data)
 *
 * @method Application[]|ICollection findAllForAdmin()
 * @method Application[]|ICollection findGroupedByEmail(array $excludeEmails = NULL)
 *
 * @method int[] findApplicationUserPairs($eventId)
 *
 * @see ApplicationMapper
 */
class ApplicationRepository extends Repository {

    static function getEntityClassNames() : array {
        return [Application::class];
    }

    /**
     * @param string $email
     * @return ICollection|Application[]
     */
    function findByEmail($email) {
        return $this->findBy(['email' => $email]);
        /*$applications = [];
        foreach($this->findBy(['hashedEmail' => Application::hashForSearch($email, 'hashedEmail')])->orderBy('createdAt', ICollection::DESC) as $application) {
            if($application->email === $email) {
                $applications[] = $application;
            }
        }
        return $applications;*/
    }
}
