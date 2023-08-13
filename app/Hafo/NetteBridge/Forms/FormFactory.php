<?php

namespace Hafo\NetteBridge\Forms;

use Nette\Application\UI\Form;

interface FormFactory {

    /**
     * @return Form
     */
    function create();

}
