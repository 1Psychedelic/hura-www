<?php

namespace Hafo\Orm\Encryption;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use Nette\InvalidStateException;

class DatabaseCryptoHelper {

    private $keysFile;

    private $loaded;

    function __construct($keysFile) {
        $this->keysFile = $keysFile;
    }

    function encrypt($table, $field, $plaintext) {
        return Crypto::encrypt($plaintext, $this->getKey($table, $field));
    }

    function decrypt($table, $field, $encrypted) {
        if(empty($encrypted)) {
            return $encrypted;
        }
        return Crypto::decrypt($encrypted, $this->getKey($table, $field));
    }

    private function getKey($table, $field) {
        if($this->loaded === NULL) {
            $this->loaded = include $this->keysFile;
        }
        if(!array_key_exists($table, $this->loaded) || !array_key_exists($field, $this->loaded[$table])) {
            throw new InvalidStateException;
        }
        return Key::loadFromAsciiSafeString($this->loaded[$table][$field]);
    }

}
