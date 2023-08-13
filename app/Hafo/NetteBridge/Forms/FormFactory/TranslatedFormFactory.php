<?php

namespace Hafo\NetteBridge\Forms\FormFactory;

use Hafo\NetteBridge\Forms;
use Nette\Localization\ITranslator;

class TranslatedFormFactory implements Forms\FormFactory  {

    private $factory;

    private $translator;

    function __construct(Forms\FormFactory $factory, ITranslator $translator) {
        $this->factory = $factory;
        $this->translator = $translator;
    }

    function create() {
        $f = $this->factory->create();
        $f->setTranslator($this->translator);
        return $f;
    }

}
