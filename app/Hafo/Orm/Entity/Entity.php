<?php

namespace Hafo\Orm\Entity;

use Hafo\DI\Container;
use Nextras\Orm\InvalidStateException;

/**
 * @property int $id
 */
abstract class Entity extends \Nextras\Orm\Entity\Entity {

    /**
     * @var Container
     */
    protected $container;

    function injectContainer(Container $container) {
        $this->container = $container;
    }

    function copyPropertiesTo(Entity $targetEntity, array $properties) {
        foreach($properties as $property) {
            $targetEntity->setValue($property, $this->getValue($property));
        }
    }

    function setValues(array $data) {
        $properties = $this->getMetadata()->getProperties();
        foreach($data as $key => $value) {
            if(array_key_exists($key, $properties)) {
                $this->setValue($key, $value);
            }
        }
    }

    function getValues(array $fields = NULL) {
        $data = [];
        $properties = $this->getMetadata()->getProperties();
        foreach($properties as $key => $property) {
            if($fields !== NULL && !in_array($key, $fields, TRUE)) {
                continue;
            };
            try {
                $data[$key] = $this->getRawValue($key);
            } catch (InvalidStateException $e) {
                // nevermind
            }
        }
        return $data;
    }

    function __toString() {
        return sprintf('#%s', $this->id);
    }

    static function hashForSearch($data, $field) {
        return sha1(self::getSearchHashSalt($field) . '|' . $data);
    }

    static protected function getSearchHashSalt($field) {
        return sha1($field);
    }

}
