<?php

namespace Hafo\Translation\Translator;

use Hafo\Translation;
use Nette\Utils\Strings;

class DefaultTranslator implements Translation\Translator  {

    private $lang;

    private $langs;

    private $vocabularies = [];

    private $translators = [];

    function __construct($langs = ['en', 'cs']) {
        $this->langs = $langs;
        foreach($langs as $l) {
            $this->vocabularies[$l] = [];
            $this->translators[$l] = [];
        }
    }

    function setLanguage($lang) {
        if(!in_array($lang, $this->langs)) {
            throw new \Exception;
        }
        $this->lang = $lang;
        return $this;
    }

    function addVocabulary($prefix, $file, $lang = 'en') {
        if(!in_array($lang, $this->langs)) {
            throw new \Exception;
        }
        $this->vocabularies[$lang][$prefix] = $file;
        return $this;
    }

    function translate($message, $count = NULL, $params = []) {
        if($this->lang === NULL || !in_array($this->lang, $this->langs)) {
            throw new \Exception;
        }
        foreach(array_keys($this->vocabularies[$this->lang]) as $prefix) {
            if(Strings::startsWith($message, $prefix)) {
                if(!array_key_exists($prefix, $this->translators[$this->lang])) {
                    $this->translators[$this->lang][$prefix] = new ArrayTranslator(include $this->vocabularies[$this->lang][$prefix]);
                }
                return $this->getTranslator($this->lang, $prefix)->translate($message, $count, $params);
            }
        }
        return $message;
    }

    /**
     * @param string $lang
     * @param string $prefix
     * @return ArrayTranslator
     */
    private function getTranslator($lang, $prefix) {
        return $this->translators[$lang][$prefix];
    }

}
