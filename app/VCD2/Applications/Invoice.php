<?php

namespace VCD2\Applications;

use Nextras\Orm\Relationships\ManyHasOne;
use Nextras\Orm\Relationships\OneHasMany;
use Nextras\Orm\Relationships\OneHasOne;
use VCD2\Applications\EntityTrait\Invoice\InvoiceInfo;
use VCD2\Entity;

/**
 * @property int $id {primary}
 *
 *
 **** Základní údaje
 * @property OneHasOne|Application|NULL $application {1:1 Application::$invoice, isMain=TRUE}
 * @property OneHasMany|InvoiceItem[] $items {1:m InvoiceItem::$invoice}
 * @property ManyHasOne|PaymentMethod|NULL $paymentMethod {m:1 PaymentMethod, oneSided=TRUE}
 * @property string|NULL $variableSymbol
 * @property string|NULL $notes
 *
 * @property string|NULL $customFile
 *
 *
 **** Údaje o odběrateli
 *
 *
 **** Časové údaje
 * @property \DateTimeImmutable $createdAt {default now}
 * @property \DateTimeImmutable|NULL $payTill
 *
 *
 **** Číslování
 * @property int $countThisYear
 * @property-read string $invoiceId
 *
 *
 **** Cena
 * @property-read int $totalPrice {virtual}
 *
 *
 **** Příznaky
 * @property bool $isPaid
 *
 *
 */
class Invoice extends Entity {

    use InvoiceInfo;

    const PAY_TILL_DEFAULT = '+14 days';

    /** @internal Use static factory methods */
    function __construct(
        $countThisYear,
        Application $application = NULL,
        PaymentMethod $paymentMethod = NULL,
        $variableSymbol = NULL,
        $name = NULL,
        $city = NULL,
        $street = NULL,
        $zip = NULL,
        $ico = NULL,
        $dic = NULL,
        $isPaid = FALSE,
        \DateTimeInterface $payTill = NULL,
        \DateTimeInterface $createdAt = NULL
    ) {
        parent::__construct();

        $this->countThisYear = $countThisYear;
        $this->setReadOnlyValue('invoiceId', (new \DateTimeImmutable)->format('Y') . str_pad($countThisYear, 5, '0', \STR_PAD_LEFT));

        if($createdAt !== NULL) {
            $this->createdAt = new \DateTimeImmutable($createdAt->format('Y-m-d H:i:s'));
        }

        $this->variableSymbol = $variableSymbol;

        $this->application = $application;
        $this->paymentMethod = $paymentMethod;

        $this->name = $name;
        $this->city = $city;
        $this->street = $street;
        $this->zip = $zip;
        $this->ico = $ico;
        $this->dic = $dic;

        $this->isPaid = $isPaid;

        if($payTill !== NULL) {
            $this->payTill = new \DateTimeImmutable($payTill->format('Y-m-d H:i:s'));
        }
    }

    /**
     * @param int $countThisYear
     * @param Application $application
     * @return Invoice
     */
    static function createFromApplication($countThisYear, Application $application) {
        // faktura pro zaměstnavatele (nezaplacená)
        if ($application->isPayingOnInvoice) {
            return new self(
                $countThisYear,
                $application,
                $application->paymentMethod,
                $application->id,
                $application->invoiceName,
                $application->invoiceCity,
                $application->invoiceStreet,
                $application->invoiceZip,
                $application->invoiceIco,
                $application->invoiceDic,
                FALSE,
                $application->appliedAt->modify(self::PAY_TILL_DEFAULT)
            );
        }

        // standardní faktura (zaplacená)
        return new self(
            $countThisYear,
            $application,
            $application->paymentMethod,
            $application->id,
            $application->name,
            $application->city,
            $application->street,
            $application->zip,
            NULL,
            NULL,
            TRUE
        );
    }

    /**
     * @param $countThisYear
     * @param $name
     * @param $city
     * @param $street
     * @param $zip
     * @param null $ico
     * @param null $dic
     * @param PaymentMethod|NULL $paymentMethod
     * @param null $variableSymbol
     * @param bool $isPaid
     * @param \DateTimeInterface|NULL $payTill
     * @param \DateTimeInterface|NULL $createdAt
     * @return Invoice
     */
    static function create(
        $countThisYear,
        $name,
        $city,
        $street,
        $zip,
        $ico = NULL,
        $dic = NULL,
        PaymentMethod $paymentMethod = NULL,
        $variableSymbol = NULL,
        $isPaid = FALSE,
        \DateTimeInterface $payTill = NULL,
        \DateTimeInterface $createdAt = NULL
    ) {
        return new self(
            $countThisYear,
            NULL,
            $paymentMethod,
            $variableSymbol,
            $name,
            $city,
            $street,
            $zip,
            $ico,
            $dic,
            $isPaid,
            $payTill,
            $createdAt
        );
    }

    protected function getterTotalPrice() {
        $total = 0;
        foreach($this->items as $item) {
            $total += $item->totalPrice;
        }
        return $total;
    }

    protected function setterApplication(Application $application = NULL) {
        if($application !== NULL) {
            $this->variableSymbol = $application->id;
        }
        return $application;
    }

    protected function getterVariableSymbol($value) {
        if(empty($value) && $this->application !== NULL) {
            return $this->application->id;
        }
        return $value;
    }

    protected function setterPayTill($value = NULL) {
        if($value === NULL) {
            return $this->createdAt->modify(self::PAY_TILL_DEFAULT);
        }
        return $value;
    }

    function __toString() {
        return sprintf('#%s', $this->invoiceId);
    }

}
