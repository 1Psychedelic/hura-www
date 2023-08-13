<?php

namespace VCD2\Credits;

use Hafo\Orm\Entity\Entity;
use VCD2\Applications\Application;
use VCD2\Users\User;

/**
 * @property int $id {primary}
 *
 *
 **** Základní údaje
 * @property User $user {m:1 User::$creditMovements}
 * @property Application|NULL $application {m:1 Application::$creditMovements}
 * @property int $difference
 * @property string|NULL $notes
 *
 *
 **** Časové údaje
 * @property \DateTimeImmutable|NULL $createdAt {default now}
 *
 *
 **** Příznaky
 * @property-read bool $isNegative {virtual}
 *
 * 
 */
class CreditMovement extends Entity {

    function __construct($difference, User $user, $notes = NULL, Application $application = NULL) {
        parent::__construct();
        
        $this->difference = $difference;
        $this->user = $user;
        $this->notes = $notes;
        $this->application = $application;
    }

    protected function getterIsNegative() {
        return $this->difference < 0;
    }

    function createReverseMovement($notes = NULL) {
        return new self(
            -$this->difference,
            $this->user,
            $notes === NULL ? sprintf('Reverse "%s"', $this->notes === NULL ? $this->id : $this->notes) : $notes,
            $this->application
        );
    }

}
