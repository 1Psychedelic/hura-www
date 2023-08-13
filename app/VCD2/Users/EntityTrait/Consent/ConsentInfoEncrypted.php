<?php
declare(strict_types=1);

namespace VCD2\Users\EntityTrait\Consent;

/**
 * @property string|NULL $email {virtual}
 * @property string $ip {virtual}
 *
 * @property string|NULL $encryptedEmail
 * @property string $encryptedIp
 */
trait ConsentInfoEncrypted
{
    protected function getterEmail()
    {
        return $this->encryptedEmail;
    }

    protected function setterEmail($value)
    {
        $this->encryptedEmail = $value;

        return $value;
    }

    protected function getterIp()
    {
        return $this->encryptedIp;
    }

    protected function setterIp($value)
    {
        $this->encryptedIp = $value;

        return $value;
    }
}
