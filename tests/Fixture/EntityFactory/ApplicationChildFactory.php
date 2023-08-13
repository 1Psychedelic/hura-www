<?php

namespace Tests\Fixture\EntityFactory;

use Hafo\Persona\Gender;
use Tests\Fixture\OrmLoader;
use VCD2\Applications\Application;
use VCD2\Applications\Child;

class ApplicationChildFactory
{

    static function createApplicationChild(Application $application, \DateTimeImmutable $dateBorn)
    {
        $child = new Child(
            $application,
            null,
            'Dítě Dítě',
            Gender::MALE,
            $dateBorn,
            true,
            false,
            'Zdravé',
            'Žádné alergie',
            'Bez komentáře'
        );
        OrmLoader::getOrm()->applicationChildren->attach($child);

        return $child;
    }

}
