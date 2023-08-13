<?php

namespace Tests\Fixture\EntityFactory;

use Tests\Fixture\OrmLoader;
use VCD2\Events\Event;

class EventFactory
{

    static function createEvent($price, $deposit, $siblingDiscount)
    {
        $event = new Event(
            Event::TYPE_TRIP,
            'Test event',
            new \DateTimeImmutable('+5 months'),
            new \DateTimeImmutable('+6 months'),
            3,      // maxParticipants
            3,      // maxReserves
            5,      // ageMin
            10,     // ageMax
            $price,
            $deposit,
            $siblingDiscount,
            14      // ageCap
        );
        OrmLoader::getOrm()->events->attach($event);

        return $event;
    }

}
