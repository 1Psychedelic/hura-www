<?php

namespace VCD2\Applications;

use VCD2\FlashMessageException;

class ApplicationException extends FlashMessageException {}

class InvalidParentInfoException extends ApplicationException {}

class InvalidChildrenException extends ApplicationException {}

class ApplicationClosedException extends ApplicationException {}

class ApplicationCapacityException extends ApplicationException {}

class ApplicationDiscountException extends ApplicationException {}

class DuplicateChildException extends ApplicationException {}

class AgeOutOfRangeException extends ApplicationException {

    public $age;

    public $min;

    public $max;

    function setAgeInfo($age, $min, $max) {
        $this->age = $age;
        $this->min = $min;
        $this->max = $max;
        return $this;
    }

}

class PaymentException extends FlashMessageException {}
