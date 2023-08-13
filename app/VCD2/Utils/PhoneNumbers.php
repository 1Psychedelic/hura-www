<?php

namespace VCD2\Utils;

use Nette\Utils\Strings;

class PhoneNumbers {

    static function normalize($phoneNumber, array $prefix = ['00420' => '+420']) {
        $phoneNumber = str_replace(' ', '', $phoneNumber);

        $altPrefix = array_keys($prefix)[0];
        if(Strings::startsWith($phoneNumber, $altPrefix)) {
            $phoneNumber = substr($phoneNumber, strlen($altPrefix));
        }

        if(!Strings::startsWith($phoneNumber, $prefix[$altPrefix])) {
            $phoneNumber = $prefix[$altPrefix] . $phoneNumber;
        }

        return $phoneNumber;
    }

}
