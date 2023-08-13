<?php
declare(strict_types=1);

namespace VCD2\Applications\EntityTrait\Application;

/**
 * @property string|NULL $encryptedInvoiceName
 * @property string|NULL $encryptedInvoiceIco
 * @property string|NULL $encryptedInvoiceDic
 * @property string|NULL $encryptedInvoiceCity
 * @property string|NULL $encryptedInvoiceStreet
 * @property string|NULL $encryptedInvoiceZip
 *
 * @property string|NULL $invoiceName {virtual}
 * @property string|NULL $invoiceIco {virtual}
 * @property string|NULL $invoiceDic {virtual}
 * @property string|NULL $invoiceCity {virtual}
 * @property string|NULL $invoiceStreet {virtual}
 * @property string|NULL $invoiceZip {virtual}
 */
trait InvoiceInfoEncrypted
{
    protected function getterInvoiceName()
    {
        return $this->encryptedInvoiceName;
    }

    protected function getterInvoiceIco()
    {
        return $this->encryptedInvoiceIco;
    }

    protected function getterInvoiceDic()
    {
        return $this->encryptedInvoiceDic;
    }

    protected function getterInvoiceCity()
    {
        return $this->encryptedInvoiceCity;
    }

    protected function getterInvoiceStreet()
    {
        return $this->encryptedInvoiceStreet;
    }

    protected function getterInvoiceZip()
    {
        return $this->encryptedInvoiceZip;
    }

    protected function setterInvoiceName($value)
    {
        $this->encryptedInvoiceName = $value;

        return $value;
    }

    protected function setterInvoiceIco($value)
    {
        $this->encryptedInvoiceIco = $value;

        return $value;
    }

    protected function setterInvoiceDic($value)
    {
        $this->encryptedInvoiceDic = $value;

        return $value;
    }

    protected function setterInvoiceCity($value)
    {
        $this->encryptedInvoiceCity = $value;

        return $value;
    }

    protected function setterInvoiceStreet($value)
    {
        $this->encryptedInvoiceStreet = $value;

        return $value;
    }

    protected function setterInvoiceZip($value)
    {
        $this->encryptedInvoiceZip = $value;

        return $value;
    }
}
