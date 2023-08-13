<?php

namespace VCD2\Discounts\Service;

use VCD2\Applications\Application;
use VCD2\Discounts\DiscountCode;
use VCD2\Discounts\DiscountCodeException;
use VCD2\Orm;

class DiscountCodes {

    private $orm;

    function __construct(Orm $orm) {
        $this->orm = $orm;
    }

    function getUsableCodeForApplication(Application $application, $code) {
        /** @var DiscountCode[] $codes */
        $codes = $this->orm->discountCodes->findUsable()->findBy(['code' => $code]);
        foreach($codes as $code) {
            try {
                $code->checkRequirementsForApplication($application);
                return $code;
            } catch (DiscountCodeException $e) {}
        }
        return NULL;
    }

}
