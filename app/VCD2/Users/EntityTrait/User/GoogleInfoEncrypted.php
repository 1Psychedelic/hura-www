<?php
declare(strict_types=1);

namespace VCD2\Users\EntityTrait\User;

/**
 * @property string|NULL $googleId {virtual}
 * @property string|NULL $googleName {virtual}
 * @property string|NULL $googleEmail {virtual}
 * @property string|NULL $googleLink {virtual}
 *
 * @property string|NULL $encryptedGoogleId
 * @property string|NULL $hashedGoogleId
 * @property string|NULL $encryptedGoogleName
 * @property string|NULL $encryptedGoogleEmail
 * @property string|NULL $encryptedGoogleLink
 */
trait GoogleInfoEncrypted
{
    protected function getterGoogleId()
    {
        return $this->encryptedGoogleId;
    }

    protected function getterGoogleName()
    {
        return $this->encryptedGoogleName;
    }

    protected function getterGoogleEmail()
    {
        return $this->encryptedGoogleEmail;
    }

    protected function getterGoogleLink()
    {
        return $this->encryptedGoogleLink;
    }

    protected function setterGoogleId($value)
    {
        $this->encryptedGoogleId = $value;

        return $value;
    }

    protected function setterEncryptedGoogleId($value)
    {
        $this->hashedGoogleId = $value === null ? null : self::hashForSearch($value, 'hashedGoogleId');

        return $value;
    }

    protected function setterGoogleName($value)
    {
        $this->encryptedGoogleName = $value;

        return $value;
    }

    protected function setterGoogleEmail($value)
    {
        $this->encryptedGoogleEmail = $value;

        return $value;
    }

    protected function setterGoogleLink($value)
    {
        $this->encryptedGoogleLink = $value;

        return $value;
    }
}
