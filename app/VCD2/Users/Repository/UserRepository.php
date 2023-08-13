<?php

namespace VCD2\Users\Repository;

use Hafo\Orm\Repository\Repository;
use Nette\Utils\Strings;
use Nextras\Orm\Collection\ArrayCollection;
use Nextras\Orm\Collection\ICollection;
use VCD2\Users\Mapper\UserMapper;
use VCD2\Users\User;

/**
 * @method UserMapper getMapper()
 *
 * @method User|NULL get($primaryKey)
 * @method User|NULL getBy(array $conds)
 *
 * @method User[]|ICollection find($ids)
 * @method User[]|ICollection findAll()
 * @method User[]|ICollection findBy(array $where)
 *
 * @method User hydrateEntity(array $data)
 *
 * @method string[] findEmails()
 */
class UserRepository extends Repository
{
    public static function getEntityClassNames() : array
    {
        return [User::class];
    }

    /**
     * @param string $email
     * @return User|NULL
     */
    public function getByEmail($email)
    {
        if (strlen($email) === 0) {
            return null;
        }

        return $this->getBy(['email' => $email]);
    }

    /** @return string[] */
    public function findSelectOptions()
    {
        $options = [];
        foreach ($this->findAll() as $user) {
            $options[$user->id] = sprintf('%s, %s%s', $user->name, $user->email, empty($user->phone) ? '' : ', ' . $user->phone);
        }

        return $options;
    }

    /** @return string[] */
    function findIdNamePairs() {
        $pairs = [];
        foreach($this->findAll() as $user) {
            $pairs[$user->id] = $user->name;
        }
        return $pairs;
    }

    /** @return string[] */
    public function findSelectOptionsForAdmin()
    {
        $pairs = [];
        foreach ($this->findAll() as $user) {
            $pairs[$user->id] = '#' . $user->id . ' ' . $user->name . ' - ' . $user->email;
        }

        return $pairs;
    }

    /** @return string[] */
    public function findAdminIdNamePairs()
    {
        $pairs = [];
        foreach ($this->findBy(['this->roles->role' => 'admin']) as $admin) {
            $pairs[$admin->id] = $admin->name;
        }

        return $pairs;
    }

    /**
     * @param string null $q
     * @return ArrayCollection|ICollection|\VCD2\Users\User[]
     */
    public function search($q = null)
    {
        $result = [];
        $all = $this->findAll();

        if ($q === null) {
            return $all;
        }

        foreach ($all as $entity) {
            if (Strings::contains($entity->email, $q) || Strings::contains($entity->name, $q) || Strings::contains($entity->phone, $q)) {
                $result[] = $entity;
            }
        }

        return new ArrayCollection($result, $this);
    }
}
