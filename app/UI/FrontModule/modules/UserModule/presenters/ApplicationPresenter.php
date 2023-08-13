<?php

namespace VCD\UI\FrontModule\UserModule;

use Hafo\NetteBridge\Forms\FormFactory;
use Nette\Application\ForbiddenRequestException;
use VCD\UI\AuthModule\LoginPresenter;

class ApplicationPresenter extends BasePresenter {

    const LINK_DEFAULT = ':Front:User:Application:default';

    protected $requireLogin = FALSE;

    function actionDefault($id, $hash = NULL) {
        if(!$this->user->isLoggedIn() && !$hash) {
            $this->redirect(LoginPresenter::LINK_DEFAULT, ['go' => $this->link('this', ['id' => $id, 'hash' => $hash])]);
            return;
        }

        $application = $this->fetchApplication($id, $hash);

        $this->addComponent(new ApplicationControl($this->container, $application), 'application');

        $this->template->titlePrefix = 'Přihláška #' . $id;
    }

    /**
     * @param $id
     * @param null $hash
     * @return NULL|\VCD2\Applications\Application
     * @throws ForbiddenRequestException
     */
    private function fetchApplication($id, $hash = NULL) {
        $condition = ['id' => $id];
        if($this->user->isLoggedIn()) {
            if($hash === NULL) {
                $condition['user'] = $this->user->id;
            } else {
                $condition['hash'] = $hash;
            }
        } else {
            $condition['hash'] = $hash;
        }

        $application = $this->orm->applications->getBy($condition);
        if($application === NULL) {
            throw new ForbiddenRequestException;
        }

        return $application;
    }

}
