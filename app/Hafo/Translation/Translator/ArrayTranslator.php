<?php

namespace Hafo\Translation\Translator;

use Hafo\Translation;

class ArrayTranslator implements Translation\Translator {

    private $translations = [];

    function __construct(array $translations = []) {
        $this->translations = $translations;
    }

    function translate($message, $count = NULL, $params = []) {
        if(is_array($count)) {
            $params = $count;
            $count = 0;
        } else {
            $count = (int)$count;
        }
        $params = array_merge(['count' => $count], $params);
        if(array_key_exists($message, $this->translations)) {
            if(is_array($this->translations[$message])) {
                while(!array_key_exists($count, $this->translations[$message]) && $count > 0) {
                    $count--;
                }
                if(array_key_exists($count, $this->translations[$message])) {
                    return $this->parametrizeText($this->translations[$message][$count], $params);
                }
            } else {
                return $this->parametrizeText($this->translations[$message], $params);
            }
        }
        return $message;
    }

    private function parametrizeText($text, $params = []) {
        $cb = function($val) {
            return '%' . $val . '%';
        };
        return str_replace(array_map($cb, array_keys($params)), array_values($params), $text);
    }

}
