<?php

namespace Hafo\NetteBridge\Database;

use Nette\Database\Table\Selection;

interface SelectionFactory {

    /** @return Selection */
    function create();

}
