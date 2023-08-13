<?php

namespace Hafo\NetteBridge\Forms\Controls;

use Nette;

class DateTimeInput extends Nette\Forms\Controls\TextInput {

    public function setValue($value) {
        return parent::setValue($value instanceof \DateTimeInterface ? $value->format('Y-m-d H:i') : $value);
    }

    /**
     * @return Nette\Utils\DateTime|NULL
     */
    public function getValue() {
        if ($this->value instanceof \DateTimeInterface) {
            return Nette\Utils\DateTime::from($this->value);
        } else if (is_int($this->value)) {
            return Nette\Utils\DateTime::from($this->value);
        } else if (empty($this->value)) {
            return NULL;
        } else if (is_string($this->value)) {
            try {
                return Nette\Utils\DateTime::from($this->value);
            } catch (\Exception $e) {
                return NULL;
            }
        }
        return NULL;
    }

}
