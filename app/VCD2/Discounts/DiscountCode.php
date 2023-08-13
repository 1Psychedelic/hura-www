<?php

namespace VCD2\Discounts;

use Hafo\Orm\Entity\Entity;
use Nextras\Orm\Collection\ICollection;
use Nextras\Orm\Relationships\ManyHasMany;
use Nextras\Orm\Relationships\OneHasMany;
use VCD2\Applications\Application;
use VCD2\Events\Event;
use VCD2\Users\User;

/**
 * @property int $id {primary}
 *
 *
 **** Základní údaje
 * @property string $code
 * @property int $discount
 * @property int $source {enum self::SOURCE_*} {default self::SOURCE_MANUAL}
 *
 *
 **** Omezení
 * @property User|NULL $forUser {m:1 User, oneSided=TRUE}
 * @property Event|NULL $forEvent {m:1 Event, oneSided=TRUE}
 * @property ManyHasMany|Event[] $forEvents {m:m Event, isMain=TRUE, oneSided=TRUE}
 * @property \DateTimeImmutable|NULL $expires
 *
 *
 **** Použití
 * @property int|NULL $maxUsages {default NULL}
 * @property int|NULL $usagesLeft {default NULL}
 * @property-read int $timesUsed {virtual}
 * @property bool $multiplyByChildren {default FALSE}
 *
 *
 **** Použito v přihláškách
 * @property OneHasMany|Application[] $applications {1:m Application::$discountCode}
 * @property ICollection|Application[] $consumedByApplications {virtual}
 *
 *
 **** Příznaky
 * @property-read bool $isUsable {virtual}
 * @property-read bool $hasUsagesLeft {virtual}
 * @property-read bool $hasExpired {virtual}
 *
 *
 **** todo Remove me from db
 * property int $timesUsed {default 0}
 *
 *
 */
class DiscountCode extends Entity {

    const SOURCE_MANUAL = 0;
    const SOURCE_REVIEW = 1;

    function __construct($code, $discount, $maxUsages = NULL, $source = self::SOURCE_MANUAL) {
        parent::__construct();

        $this->code = $code;
        $this->discount = $discount;
        $this->maxUsages = $this->usagesLeft = $maxUsages;
        $this->source = $source;
    }

    function discountValueFor($countChildren) {
        return $this->discount * ($this->multiplyByChildren ? $countChildren : 1);
    }
    
    function checkRequirementsForApplication(Application $application) {
        // rejected by application
        if(!$application->canUseDiscountCode) {
            throw new DiscountCodeRejectedException;
        }

        // expiration&usages
        if(!$this->isUsable) {
            throw new DiscountCodeExpiredException;
        }

        // events restriction
        if($this->forEvents->count() > 0 && $this->forEvents->get()->getBy(['id' => $application->event->id]) === NULL) {
            throw new DiscountCodeRejectedException;
        }

        // user restriction
        if($this->forUser !== NULL && $this->forUser !== $application->user) {
            throw new DiscountCodeRejectedException;
        }
    }

    protected function getterIsUsable() {
        return $this->hasUsagesLeft && !$this->hasExpired;
    }

    protected function getterHasUsagesLeft() {
        return $this->usagesLeft === NULL || $this->usagesLeft > 0;
    }

    protected function getterHasExpired() {
        return $this->expires !== NULL && $this->expires < new \DateTime;
    }

    /*protected function getterUsagesLeft() {
        return $this->maxUsages === NULL ? NULL : $this->maxUsages - $this->timesUsed;
    }*/

    protected function getterTimesUsed() {
        return $this->consumedByApplications->count();
    }

    protected function getterConsumedByApplications() {
        return $this->applications->get()->findBy(['appliedAt!=' => NULL, 'appliedAt<=' => new \DateTime]);
    }

    function recalculateUsagesLeft() {
        $this->usagesLeft = $this->maxUsages === NULL ? NULL : $this->maxUsages - $this->timesUsed;
    }

}
