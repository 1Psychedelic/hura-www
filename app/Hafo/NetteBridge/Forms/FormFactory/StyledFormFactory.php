<?php

namespace Hafo\NetteBridge\Forms\FormFactory;

use Hafo\NetteBridge\Forms;

class StyledFormFactory implements Forms\FormFactory  {

    private $factory;

    private $rendererFactory;

    function __construct(Forms\FormFactory $factory, callable $rendererFactory) {
        $this->factory = $factory;
        $this->rendererFactory = $rendererFactory;
    }

    function create() {
        $f = $this->factory->create();
        $f->setRenderer(call_user_func($this->rendererFactory));
        return $f;
    }

}
