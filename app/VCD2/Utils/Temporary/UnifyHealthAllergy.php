<?php

namespace VCD2\Utils\Temporary;

use VCD2\Orm;

class UnifyHealthAllergy {

    private $orm;

    function __construct(Orm $orm) {
        $this->orm = $orm;
    }

    function execute() {
        $children = $this->orm->children->findAll();
        foreach($children as $child) {
            if(strlen($child->health) > 0 && strlen($child->allergy) > 0) {
                $child->health = $this->concatenate($child->health, $child->allergy);
                $child->allergy = NULL;
                $this->orm->persist($child);
            }
        }
        $this->orm->flush();

        $applicationChildren = $this->orm->applicationChildren->findAll();
        foreach($applicationChildren as $child) {
            if(strlen($child->health) > 0 && strlen($child->allergy) > 0) {
                $child->health = $this->concatenate($child->health, $child->allergy);
                $child->allergy = NULL;
                $this->orm->persist($child);
            }
        }
        $this->orm->flush();
    }

    private function concatenate($health, $allergy) {
        return $health . "\n\nAlergie: " . $allergy;
    }

}
