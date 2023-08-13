<?php
declare(strict_types=1);

namespace VCD2\Applications\EntityTrait\Application;

/**
 * @property string $name {virtual}
 * @property string|NULL $email {virtual}
 * @property string|NULL $phone {virtual}
 * @property string|NULL $city {virtual}
 * @property string|NULL $street {virtual}
 * @property string|NULL $zip {virtual}
 *
 * @property string|NULL $encryptedName
 * @property string|NULL $encryptedEmail
 * @property string|NULL $hashedEmail
 * @property string|NULL $encryptedPhone
 * @property string|NULL $encryptedCity
 * @property string|NULL $encryptedStreet
 * @property string|NULL $encryptedZip
 */
trait ParentInfoEncrypted
{
    protected function getterName()
    {
        return $this->encryptedName;
    }

    protected function getterEmail()
    {
        return $this->encryptedEmail;
    }

    protected function getterCity()
    {
        return $this->encryptedCity;
    }

    protected function getterPhone()
    {
        return $this->encryptedPhone;
    }

    protected function getterStreet()
    {
        return $this->encryptedStreet;
    }

    protected function getterZip()
    {
        return $this->encryptedZip;
    }

    protected function setterName($value)
    {
        $this->encryptedName = $value;

        return $value;
    }

    protected function setterEmail($value)
    {
        $this->encryptedEmail = $value;

        return $value;
    }

    protected function setterEncryptedEmail($value)
    {
        $this->hashedEmail = $value === null ? null : self::hashForSearch($value, 'hashedEmail');

        return $value;
    }

    protected function setterPhone($value)
    {
        $this->encryptedPhone = $value;

        return $value;
    }

    protected function setterCity($value)
    {
        $this->encryptedCity = $value;

        return $value;
    }

    protected function setterStreet($value)
    {
        $this->encryptedStreet = $value;

        return $value;
    }

    protected function setterZip($value)
    {
        $this->encryptedZip = $value;

        return $value;
    }
}
