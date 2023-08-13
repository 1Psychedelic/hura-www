<?php

namespace VCD2\Events;

use Hafo\Orm\Entity\Entity;
use Nextras\Orm\Collection\ICollection;

/**
 * @property int $id {primary}
 *
 *
 **** Základní údaje
 * @property ApplicationStep $step {m:1 ApplicationStep::$options}
 * @property int $position
 * @property string $option
 *
 *
 **** Cena
 * @property int $price
 * @property bool $absolutePrice
 * @property bool $multiplyByChildren
 * @property bool $allowSiblingDiscount
 * @property bool $allowDiscountCodes
 *
 *
 **** Kapacita
 * @property int|NULL $maxUsages
 * @property-read int $timesUsed {virtual}
 * @property-read int $timesUsedAccepted {virtual}
 * @property-read int|NULL $freeCapacity {virtual}
 *
 *
 */
class ApplicationStepOption extends Entity {

    function __construct(ApplicationStep $step, $option, $price, $multiplyByChildren, $allowSiblingDiscount, $allowDiscountCodes, $absolutePrice, $maxUsages = NULL) {
        parent::__construct();
        
        $this->step = $step;
        $this->option = $option;
        $this->price = $price;
        $this->multiplyByChildren = $multiplyByChildren;
        $this->allowSiblingDiscount = $allowSiblingDiscount;
        $this->allowDiscountCodes = $allowDiscountCodes;
        $this->absolutePrice = $absolutePrice;
        $this->maxUsages = $maxUsages;

        $topOption = $step->options->get()->orderBy(['position' => ICollection::DESC])->fetch();
        $this->position = $topOption === NULL ? 0 : $topOption->position + 1;
    }

    protected function getterFreeCapacity() {
        return $this->maxUsages === NULL ? NULL : max(0, $this->maxUsages - $this->timesUsed);
    }

    protected function getterTimesUsed() {
        $count = 0;
        foreach($this->step->event->appliedChildren as $child) {
            $choice = $child->application->stepChoices->get()->getBy(['this->option->id' => $this->id]);
            if($choice !== NULL) {
                $count++;
            }
        }
        return $count;
    }

    protected function getterTimesUsedAccepted() {
        $count = 0;
        foreach($this->step->event->acceptedChildren as $child) {
            $choice = $child->application->stepChoices->get()->getBy(['this->option->id' => $this->id]);
            if($choice !== NULL) {
                $count++;
            }
        }
        return $count;
    }
    
}
