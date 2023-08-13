<?php

namespace VCD2\Users;

use Hafo\Orm\Entity\Entity;
use Nextras\Orm\Relationships\ManyHasMany;
use Nextras\Orm\Relationships\OneHasMany;
use VCD2\Applications\Application;
use VCD2\Events\Event;
use VCD2\Users\EntityTrait\Child\ChildInfo;

/**
 * @property int $id {primary}
 *
 *
 **** Údaje o dítěti
 * @property \DateTimeImmutable $dateBorn
 * @property string $gender {enum \Hafo\Persona\Gender::*} {default \Hafo\Persona\Gender::UNKNOWN}
 * @property bool $swimmer
 * @property bool $adhd
 * @property string|NULL $health
 * @property string|NULL $allergy
 * @property string|NULL $notes
 *
 *
 **** Zákonný zástupce
 * @property User|NULL $parent {m:1 User, oneSided=TRUE}
 * @property ManyHasMany|User[] $parents {m:m User::$children, isMain=TRUE}
 *
 *
 **** Přihlášky
 * @property OneHasMany|\VCD2\Applications\Child[] $applicationChildren {1:m \VCD2\Applications\Child::$child, orderBy=[id=DESC]}
 * @property-read Application[] $applications {virtual}
 * @property-read Application[] $appliedApplications {virtual}
 *
 *
 **** Účast na akcích
 * @property-read Event[] $eventsParticipated {virtual}
 *
 *
 **** Diplomy
 * @property-read \VCD2\Applications\Child[] $potentialDiplomas {virtual}
 * @property-read \VCD2\Applications\Child[] $diplomas {virtual}
 *
 *
 **** Příznaky
 * @property-read bool $isEditableByUser {virtual}
 *
 *
 **** Deprecated
 * @property string|NULL $photo
 *
 *
 */
class Child extends Entity {

    use ChildInfo;

    function __construct(User $parent, $name, $gender, \DateTimeImmutable $dateBorn, $swimmer, $adhd, $health = NULL, $allergy = NULL, $notes = NULL) {
        parent::__construct();

        $this->parent = $parent;
        $this->parents->add($parent);

        $this->updateInfo($name, $gender, $dateBorn, $swimmer, $adhd, $health, $allergy, $notes);
    }

    function updateInfo($name, $gender, \DateTimeImmutable $dateBorn, $swimmer, $adhd, $health = NULL, $allergy = NULL, $notes = NULL) {
        $this->name = $name;
        $this->gender = $gender;
        $this->dateBorn = $dateBorn;
        $this->swimmer = $swimmer;
        $this->adhd = $adhd;
        $this->health = $health;
        $this->allergy = $allergy;
        $this->notes = $notes;
    }

    protected function setterDateBorn(\DateTimeImmutable $value) {
        $value = $value->setTime(12, 0);
        return $value;
    }

    protected function getterPotentialDiplomas() {
        $result = [];
        foreach($this->applicationChildren as $applicationChild) {
            $event = $applicationChild->application->event;
            if(!$event->hasEnded) {
                continue;
            }
            
            if($applicationChild->application->isAccepted) {
                $result[] = $applicationChild;
            }
        }
        return $result;
    }

    protected function getterDiplomas() {
        $result = [];
        foreach($this->applicationChildren as $applicationChild) {
            $event = $applicationChild->application->event;
            if(!$event->hasEnded) {
                continue;
            }

            if($applicationChild->hasDiploma) {
                $result[] = $applicationChild;
            }
        }
        return $result;
    }

    protected function getterApplications() {
        $result = [];
        foreach($this->applicationChildren as $applicationChild) {
            $result[] = $applicationChild->application;
        }
        return $result;
    }

    protected function getterAppliedApplications() {
        $result = [];
        foreach($this->applications as $application) {
            if(!$application->isDraft) {
                $result[] = $application;
            }
        }
        return $result;
    }

    protected function getterIsEditableByUser() {
        return count($this->appliedApplications) === 0;
    }
    
    protected function getterEventsParticipated() {
        $result = [];
        foreach($this->potentialDiplomas as $applicationChild) {

            $event = $applicationChild->application->event;
            if(!$event->hasEnded) {
                continue;
            }

            $result[] = $event;
        }
        return $result;
    }

    static function createFromArray(User $user, array $data) {
        $dateBorn = $data['dateBorn'] instanceof \DateTimeInterface ? $data['dateBorn']->format('Y-m-d H:i:s') : $data['dateBorn'];
        return new self(
            $user,
            $data['name'],
            $data['gender'],
            new \DateTimeImmutable($dateBorn),
            $data['swimmer'],
            $data['adhd'],
            $data['health'],
            NULL,
            isset($data['notes']) ? $data['notes'] : null
        );
    }

    static function createFromApplicationChild(\VCD2\Applications\Child $child, User $user = NULL) {
        if($child->application->user === NULL && $user === NULL) {
            throw InvalidChildException::create('Child belongs to unregistered user.');
        }
        return new self(
            $child->application->user === NULL ? $user : $child->application->user,
            $child->name,
            $child->gender,
            $child->dateBorn,
            $child->swimmer,
            $child->adhd,
            $child->health,
            NULL,
            $child->notes
        );
    }

}
