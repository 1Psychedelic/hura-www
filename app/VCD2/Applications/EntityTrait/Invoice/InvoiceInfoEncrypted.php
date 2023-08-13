<?php
declare(strict_types=1);

namespace VCD2\Applications\EntityTrait\Invoice;

/**
 * @property string $name {virtual}
 * @property string $city {virtual}
 * @property string $street {virtual}
 * @property string $zip {virtual}
 * @property string|NULL $ico {virtual}
 * @property string|NULL $dic {virtual}
 *
 * @property string $encryptedName
 * @property string $encryptedCity
 * @property string $encryptedStreet
 * @property string $encryptedZip
 * @property string|NULL $encryptedIco
 * @property string|NULL $encryptedDic
 */
trait InvoiceInfoEncrypted
{
    protected function getterName()
    {
        return $this->encryptedName;
    }

    protected function getterCity()
    {
        return $this->encryptedCity;
    }

    protected function getterStreet()
    {
        return $this->encryptedStreet;
    }

    protected function getterZip()
    {
        return $this->encryptedZip;
    }

    protected function getterIco()
    {
        return $this->encryptedIco;
    }

    protected function getterDic()
    {
        return $this->encryptedDic;
    }

    protected function setterName($value)
    {
        $this->encryptedName = $value;

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

    protected function setterIco($value)
    {
        $this->encryptedIco = $value;

        return $value;
    }

    protected function setterDic($value)
    {
        $this->encryptedDic = $value;

        return $value;
    }
}
