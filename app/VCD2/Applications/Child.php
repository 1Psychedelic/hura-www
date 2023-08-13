<?php

namespace VCD2\Applications;

use Hafo\Orm\Entity\Entity;
use Hafo\Persona\Age;
use Hafo\Persona\HumanAge;
use VCD2\Applications\EntityTrait\Child\ChildInfo;

/**
 * @property int $id {primary}
 *
 *
 **** Základní údaje
 * @property Application $application {m:1 Application::$children}
 * @property \VCD2\Users\Child|NULL $child {m:1 \VCD2\Users\Child::$applicationChildren}
 *
 *
 **** Údaje o dítěti
 * @property \DateTimeImmutable $dateBorn
 * @property string $gender {enum \Hafo\Persona\Gender::*} {default \Hafo\Persona\Gender::UNKNOWN}
 * @property bool $swimmer {default FALSE}
 * @property bool $adhd {default FALSE}
 *
 *
 **** Diplom
 * @property string|NULL $diploma
 * @property string|NULL $diplomaThumb
 *
 *
 **** Příznaky
 * @property bool $isReserve {default FALSE}
 * @property-read bool $hasDiploma {virtual}
 * @property-read bool $hasValidInfo {virtual}
 * @property-read bool $isWithinRecommendedAge {virtual}
 *
 *
 **** Časové údaje
 * @property \DateTimeImmutable|NULL $createdAt {default now}
 * @property \DateTimeImmutable|NULL $updatedAt {default now}
 *
 *
 **** Deprecated
 * @property string|NULL $photo
 * @property \DateTimeImmutable|NULL $appliedAt {default NULL}
 * @property \DateTimeImmutable|NULL $paidAt {default NULL}
 * @property \DateTimeImmutable|NULL $acceptedAt {default NULL}
 * @property \DateTimeImmutable|NULL $rejectedAt {default NULL}
 * @property \DateTimeImmutable|NULL $canceledAt {default NULL}
 *
 *
 */
class Child extends Entity {

    use ChildInfo;

    static $validateEventAgeRestriction = TRUE;

    /**
     * @param Application $application
     * @param \VCD2\Users\Child|NULL $child
     * @param $name
     * @param $gender
     * @param \DateTimeImmutable $dateBorn
     * @param $swimmer
     * @param $health
     * @param $allergy
     * @param $notes
     * @throws DuplicateChildException
     * @throws AgeOutOfRangeException
     * @throws ApplicationCapacityException
     */
    function __construct(Application $application, \VCD2\Users\Child $child = NULL, $name, $gender, \DateTimeImmutable $dateBorn, $swimmer, $adhd, $health = NULL, $allergy = NULL, $notes = NULL) {
        parent::__construct();

        $this->validateEventAgeRestriction(new HumanAge($dateBorn), $application);

        // validate capacity
        if(!$application->event->hasEnoughCapacityFor($application->children->count() + 1)) {
            throw ApplicationCapacityException::create('Not enough capacity.', 'Bohužel pro víc dětí už nemáme kapacitu.');
        }

        $this->application = $application;
        $this->child = $child;

        $this->updateInfo($name, $gender, $dateBorn, $swimmer, $adhd, $health, $allergy, $notes);

        $this->application->recalculatePrice();
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

    protected function getterHasDiploma() {
        return strlen($this->diploma) && strlen($this->diplomaThumb);
    }

    protected function getterHasValidInfo() {
        return strlen($this->name) > 3;
    }

    protected function getterIsWithinRecommendedAge() {
        $age = new HumanAge($this->dateBorn);
        $ageAtStart = $age->yearsAt($this->application->event->starts);
        $ageAtEnd = $age->yearsAt($this->application->event->ends);
        return $this->application->event->ageMin <= $ageAtEnd && $this->application->event->ageMax >= $ageAtStart;
    }

    protected function validateEventAgeRestriction(Age $age, Application $application) {
        if(self::$validateEventAgeRestriction) {
            $ageAtStart = $age->yearsAt($application->event->starts);
            $ageAtEnd = $age->yearsAt($application->event->ends);
            $ageMin = $application->event->ageMin;
            $ageMax = $application->event->ageMax;
            $ageCap = $application->event->ageCap;
            if($ageAtStart > $ageCap || $ageAtEnd < $ageMin) {
                $errorAge = $ageAtStart > $ageCap ? $ageAtStart : $ageAtEnd;
                if($ageMax === $ageCap) {
                    $e = AgeOutOfRangeException::create(
                        sprintf('Age %s is out of allowed range %s-%s.', $errorAge, $ageMin, $ageCap),
                        sprintf('Věk dítěte %s let je mimo povolený věkový rozsah %s-%s let.', $errorAge, $ageMin, $ageCap)
                    );
                    $e->setAgeInfo($errorAge, $ageMin, $ageCap);
                    throw $e;
                } else {
                    $e = AgeOutOfRangeException::create(
                        sprintf('Age %s is out of allowed range %s-%s.', $errorAge, $ageMin, $ageCap),
                        sprintf('Věk dítěte %s let je mimo povolený věkový rozsah pro tuto akci.', $errorAge)
                    );
                    $e->setAgeInfo($errorAge, $ageMin, $ageCap);
                    throw $e;
                }
            }
        }
    }

    static function createFromArray(Application $application, \VCD2\Users\Child $child = NULL, array $data) {
        $dateBorn = $data['dateBorn'] instanceof \DateTimeInterface ? $data['dateBorn']->format('Y-m-d H:i:s') : $data['dateBorn'];
        return new self(
            $application,
            $child,
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

    static function createFromUserChild(Application $application, \VCD2\Users\Child $child) {
        return new self(
            $application,
            $child,
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

    static function createFromApplicationChild(Application $application, Child $child) {
        return new self(
            $application,
            $child->child,
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
