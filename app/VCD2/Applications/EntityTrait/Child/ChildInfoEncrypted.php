<?php
declare(strict_types=1);

namespace VCD2\Applications\EntityTrait\Child;

/**
 * @property string $name {virtual}
 * @property string|NULL $health {virtual}
 * @property string|NULL $allergy {virtual}
 * @property string|NULL $notes {virtual}
 *
 * @property string|NULL $encryptedName
 * @property string|NULL $encryptedHealth
 * @property string|NULL $encryptedAllergy
 * @property string|NULL $encryptedNotes
 */
trait ChildInfoEncrypted
{
    protected function getterName()
    {
        return $this->encryptedName;
    }

    protected function getterHealth()
    {
        return $this->encryptedHealth;
    }

    protected function getterAllergy()
    {
        return $this->encryptedAllergy;
    }

    protected function getterNotes()
    {
        return $this->encryptedNotes;
    }

    protected function setterName($value)
    {
        $this->encryptedName = $value;

        return $value;
    }

    protected function setterHealth($value)
    {
        $this->encryptedHealth = $value;

        return $value;
    }

    protected function setterAllergy($value)
    {
        $this->encryptedAllergy = $value;

        return $value;
    }

    protected function setterNotes($value)
    {
        $this->encryptedNotes = $value;

        return $value;
    }
}
