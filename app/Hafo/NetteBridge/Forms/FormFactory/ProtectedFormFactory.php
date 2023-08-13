<?php

namespace Hafo\NetteBridge\Forms\FormFactory;

use Hafo\NetteBridge\Forms;

class ProtectedFormFactory implements Forms\FormFactory  {

    private $factory;

    function __construct(Forms\FormFactory $factory) {
        $this->factory = $factory;
    }

    function create() {
        $f = $this->factory->create();
        $f->addProtection();
        return $f;
    }

}
