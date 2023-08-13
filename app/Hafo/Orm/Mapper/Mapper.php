<?php

namespace Hafo\Orm\Mapper;

use Hafo\Orm\Encryption\DatabaseCryptoHelper;

abstract class Mapper extends \Nextras\Orm\Mapper\Mapper {

    protected $encrypted = [];

    /**
     * @var DatabaseCryptoHelper|NULL
     */
    protected $cryptoHelper;

    function setCryptoHelper(DatabaseCryptoHelper $cryptoHelper) {
        $this->cryptoHelper = $cryptoHelper;
        return $this;
    }

    protected function createStorageReflection() {
        $refl = parent::createStorageReflection();
        foreach($this->encrypted as $property) {
            $refl->setMapping(
                $property,
                $refl->convertEntityToStorageKey($property),
                function($encrypted) use ($property) {
                    return $encrypted === NULL ? NULL : $this->cryptoHelper->decrypt($this->getTableName(), $property, $encrypted);
                },
                function($plain) use ($property) {
                    return $plain === NULL ? NULL : $this->cryptoHelper->encrypt($this->getTableName(), $property, $plain);
                }
            );
        }
        return $refl;
    }

    function find($ids) {
        if(empty($ids)) {
            return [];
        }
        $table = $this->getTableName();
        return $this->toCollection($this->builder()
            ->where('%table.[id] IN %i[]', $table, $ids)
            ->orderBy('FIELD(%table.[id]' . str_repeat(', %i', count($ids)) . ')', $table, ...$ids));
    }

}
