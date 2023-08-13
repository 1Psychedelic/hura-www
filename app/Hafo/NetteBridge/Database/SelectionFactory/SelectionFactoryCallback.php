<?php

namespace Hafo\NetteBridge\Database\SelectionFactory;

use Hafo\NetteBridge\Database;

final class SelectionFactoryCallback implements Database\SelectionFactory {

    private $callback;

    function __construct(callable $callback) {
        $this->callback = $callback;
    }

    function create() {
        return call_user_func($this->callback);
    }

}
