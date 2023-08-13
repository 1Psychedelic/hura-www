<?php

namespace Hafo\Translation;

use Nette\Localization\ITranslator;

interface Translator extends ITranslator {

    /**
     * @param string $message
     * @param int|null $count Can be omitted
     * @param array $params
     * @return string
     */
    function translate($message, $count = NULL, $params = []);

}
