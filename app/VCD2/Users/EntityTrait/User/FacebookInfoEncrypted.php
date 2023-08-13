<?php
declare(strict_types=1);

namespace VCD2\Users\EntityTrait\User;

/**
 * @property string|NULL $facebookId {virtual}
 * @property string|NULL $facebookName {virtual}
 * @property string|NULL $facebookFirstName {virtual}
 * @property string|NULL $facebookMiddleName {virtual}
 * @property string|NULL $facebookLastName {virtual}
 * @property string|NULL $facebookLink {virtual}
 * @property string|NULL $facebookEmail {virtual}
 * @property string|NULL $facebookThirdPartyId {virtual}
 *
 * @property string|NULL $encryptedFacebookId
 * @property string|NULL $hashedFacebookId
 * @property string|NULL $encryptedFacebookName
 * @property string|NULL $encryptedFacebookFirstName
 * @property string|NULL $encryptedFacebookMiddleName
 * @property string|NULL $encryptedFacebookLastName
 * @property string|NULL $encryptedFacebookLink
 * @property string|NULL $encryptedFacebookEmail
 * @property string|NULL $encryptedFacebookThirdPartyId
 */
trait FacebookInfoEncrypted
{
    protected function getterFacebookId()
    {
        return $this->encryptedFacebookId;
    }

    protected function getterFacebookName()
    {
        return $this->encryptedFacebookName;
    }

    protected function getterFacebookFirstName()
    {
        return $this->encryptedFacebookFirstName;
    }

    protected function getterFacebookMiddleName()
    {
        return $this->encryptedFacebookMiddleName;
    }

    protected function getterFacebookLastName()
    {
        return $this->encryptedFacebookLastName;
    }

    protected function getterFacebookLink()
    {
        return $this->encryptedFacebookLink;
    }

    protected function getterFacebookEmail()
    {
        return $this->encryptedFacebookEmail;
    }

    protected function getterFacebookThirdPartyId()
    {
        return $this->encryptedFacebookThirdPartyId;
    }

    protected function setterFacebookId($value)
    {
        $this->encryptedFacebookId = $value;

        return $value;
    }

    protected function setterEncryptedFacebookId($value)
    {
        $this->hashedFacebookId = $value === null ? null : self::hashForSearch($value, 'hashedFacebookId');

        return $value;
    }

    protected function setterFacebookName($value)
    {
        $this->encryptedFacebookName = $value;

        return $value;
    }

    protected function setterFacebookFirstName($value)
    {
        $this->encryptedFacebookFirstName = $value;

        return $value;
    }

    protected function setterFacebookMiddleName($value)
    {
        $this->encryptedFacebookMiddleName = $value;

        return $value;
    }

    protected function setterFacebookLastName($value)
    {
        $this->encryptedFacebookLastName = $value;

        return $value;
    }

    protected function setterFacebookLink($value)
    {
        $this->encryptedFacebookLink = $value;

        return $value;
    }

    protected function setterFacebookEmail($value)
    {
        $this->encryptedFacebookEmail = $value;

        return $value;
    }

    protected function setterFacebookThirdPartyId($value)
    {
        $this->encryptedFacebookThirdPartyId = $value;

        return $value;
    }
}
