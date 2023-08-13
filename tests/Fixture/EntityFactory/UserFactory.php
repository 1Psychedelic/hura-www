<?php

namespace Tests\Fixture\EntityFactory;

use Tests\Fixture\OrmLoader;
use VCD2\Users\User;

class UserFactory
{

    static function createUser()
    {
        $user = new User('lukas@volnycasdeti.cz', 'Lukáš Klika');
        OrmLoader::getOrm()->users->attach($user);

        return $user;
    }

}
