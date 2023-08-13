<?php

namespace VCD2\Utils\Temporary;

use VCD2\Discounts\DiscountCode;
use VCD2\Events\Event;
use VCD2\Orm;

class ImportDiscountCodes
{

    const CAMPS = [500];

    const ALL = [300, 250, 200, 150, 100, 50, 25];

    /** @var Orm */
    private $orm;

    public function __construct(Orm $orm)
    {
        $this->orm = $orm;
    }

    function import()
    {
        $allEvents = $this->orm->events->findUpcoming()->fetchPairs('id', 'id');
        $camps = $this->orm->events->findUpcoming(Event::TYPE_CAMP)->fetchPairs('id', 'id');

        foreach (self::CAMPS as $value) {
            $codes = array_map('trim', array_unique(array_filter(explode("\n", file_get_contents(__DIR__ . '/discountcodes/camps' . $value . '.csv')))));

            foreach ($codes as $code) {
                $discountCode = new DiscountCode($code, $value, 1);
                $this->orm->discountCodes->attach($discountCode);
                foreach ($camps as $camp) {
                    $discountCode->forEvents->add($camp);
                }
                $this->orm->persist($discountCode);
            }
        }

        foreach (self::ALL as $value) {
            $codes = array_map('trim', array_unique(array_filter(explode("\n", file_get_contents(__DIR__ . '/discountcodes/all' . $value . '.csv')))));
            foreach ($codes as $code) {
                $discountCode = new DiscountCode($code, $value, 1);
                $this->orm->discountCodes->attach($discountCode);
                foreach ($allEvents as $event) {
                    $discountCode->forEvents->add($event);
                }
                $this->orm->persist($discountCode);
            }
        }

        $this->orm->flush();
    }

}
