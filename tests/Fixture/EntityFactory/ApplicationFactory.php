<?php

namespace Tests\Fixture\EntityFactory;

use Tests\Fixture\OrmLoader;
use VCD2\Applications\Application;
use VCD2\Events\Event;
use VCD2\Users\User;

class ApplicationFactory
{

    static function createApplication(Event $event, User $user)
    {
        $application = new Application($event, $user);
        OrmLoader::getOrm()->applications->attach($application);

        return $application;
    }

}
