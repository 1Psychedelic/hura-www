<?php

namespace VCD\UI\AuthModule;

use VCD\UI\FrontModule\HomepageModule\HomepagePresenter;

class LoginPresenter extends BasePresenter {

    const LINK_DEFAULT = ':Auth:Login:default';

    function actionDefault($go = NULL) {
        if($this->user->isLoggedIn()) {
            if($go === NULL) {
                $this->redirect(HomepagePresenter::LINK_DEFAULT);
            } else {
                $this->redirectUrl($go);
            }
        }
        if($go !== NULL) {
            $this['user']->setRedirectUrl($go);
        }
        $this->template->go = $go;
        $this->template->titlePrefix = 'Přihlášení';
    }

}
