<?php

namespace VCD\UI\FrontModule\UserModule;

use VCD\UI\AuthModule\LoginPresenter;

abstract class BasePresenter extends \VCD\UI\FrontModule\BasePresenter {

    protected $requireLogin = TRUE;

    function startup() {
        parent::startup();
        if($this->requireLogin && !$this->user->isLoggedIn()) {
            $this->redirect(LoginPresenter::LINK_DEFAULT, ['go' => $this->link('this')]);
        }
    }

}
