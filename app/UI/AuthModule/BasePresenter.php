<?php

namespace VCD\UI\AuthModule;

use Nette\Application\ForbiddenRequestException;

abstract class BasePresenter extends \VCD\UI\BasePresenter {

    public function startup()
    {
        throw new ForbiddenRequestException();

        parent::startup();
    }

    function beforeRender() {
        $this->setLayout(__DIR__ . '/../FrontModule/@layout.latte');
    }

}
