<?php

namespace Hafo\NetteBridge\Forms\FormFactory;

use Hafo\NetteBridge\Forms;
use Nette\Application\UI\Form;

class SimpleFormFactory implements Forms\FormFactory {

    function create() {
        return new Form;
    }

}
