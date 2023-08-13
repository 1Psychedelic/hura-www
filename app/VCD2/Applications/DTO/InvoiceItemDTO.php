<?php

namespace VCD2\Applications\DTO;

class InvoiceItemDTO {

    public $name;

    public $basePrice;

    public $amount;

    public $totalPrice;

    function __construct($name, $basePrice, $amount, $totalPrice) {
        $this->name = $name;
        $this->basePrice = $basePrice;
        $this->amount = $amount;
        $this->totalPrice = $totalPrice;
    }

}
