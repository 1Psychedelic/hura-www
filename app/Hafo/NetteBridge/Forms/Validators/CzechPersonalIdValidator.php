<?php

namespace Hafo\NetteBridge\Forms\Validators;

use Hafo\Persona\CzechPersonalId;
use Hafo\Persona\InvalidPersonalIdException;
use Nette\Forms\IControl;

final class CzechPersonalIdValidator {

    static function getRule() {
        return self::class . '::validate';
    }

    static function validate($rc) {
        $rc = $rc instanceof IControl ? $rc->getValue() : $rc;
        try {
            new CzechPersonalId($rc);
        } catch (InvalidPersonalIdException $e) {
            return FALSE;
        }
        return TRUE;
    }

}
