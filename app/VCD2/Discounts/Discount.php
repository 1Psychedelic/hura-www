<?php

namespace VCD2\Discounts;

use Hafo\Orm\Entity\Entity;
use Nette\Utils\DateTime;
use Nextras\Dbal\Utils\DateTimeImmutable;
use Nextras\Orm\Relationships\ManyHasOne;
use Nextras\Orm\Relationships\OneHasMany;
use VCD2\Applications\Application;
use VCD2\Events\Event;

/**
 * @property int $id {primary}
 *
 *
 **** Základní údaje
 * @property Event|ManyHasOne $event {m:1 Event::$discounts}
 * @property int $price
 * @property int $priceVip
 * @property bool $isDiscount {default TRUE}
 * @property bool $allowDiscountCodes {default TRUE}
 * @property bool $allowSiblingDiscount {default TRUE}
 * @property bool $allowCredits {default TRUE}
 * @property OneHasMany|Application[] $applications {1:m Application::$discount}
 *
 **** Časové údaje
 * @property \DateTimeImmutable $starts
 * @property \DateTimeImmutable $ends
 */
class Discount extends Entity
{
    public function __construct(Event $event, \DateTimeInterface $starts, \DateTimeInterface $ends, int $price, int $priceVip, bool $isDiscount)
    {
        parent::__construct();

        $this->event = $event;
        $this->starts = DateTimeImmutable::createFromMutable(DateTime::from($starts));
        $this->ends = DateTimeImmutable::createFromMutable(DateTime::from($ends));
        $this->price = $price;
        $this->priceVip = $priceVip;
        $this->isDiscount = $isDiscount;
    }

    public function isValidAt(\DateTimeInterface $when)
    {
        return $this->starts < $when && $this->ends > $when;
    }

    public function checkRequirementsForApplication(Application $application, \DateTimeInterface $when)
    {
        // check event
        if ($application->event !== $this->event) {
            throw new DiscountRejectedException;
        }

        // check expiration
        if (!$this->isValidAt($when)) {
            throw new DiscountExpiredException;
        }
    }

    public function onBeforePersist()
    {
        $this->event->updatedAt = new \DateTimeImmutable();
    }
}
