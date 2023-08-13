<?php

namespace VCD2\Applications;

use VCD2\Entity;

/**
 * @property int $id {primary}
 *
 *
 **** Základní údaje
 * @property Invoice $invoice {m:1 Invoice::$items}
 *
 *
 **** Údaje o položce
 * @property string $name
 * @property int $basePrice
 * @property int $amount
 * @property int $totalPrice
 *
 *
 */
class InvoiceItem extends Entity {

    function __construct(Invoice $invoice, $name, $basePrice, $amount, $totalPrice = NULL) {
        parent::__construct();

        $this->invoice = $invoice;
        $this->name = $name;
        $this->basePrice = $basePrice;
        $this->amount = $amount;
        $this->totalPrice = $totalPrice === NULL ? $basePrice * $amount : $totalPrice;
    }

}
