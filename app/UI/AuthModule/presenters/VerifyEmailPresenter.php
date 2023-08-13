<?php

namespace VCD\UI\AuthModule;

use Hafo\Security\Authentication\EmailAlreadyVerifiedException;
use Hafo\Security\SecurityException;
use Hafo\Security\Storage\Emails;

class VerifyEmailPresenter extends BasePresenter {

    const LINK_DEFAULT = ':Auth:VerifyEmail:default';

    function actionDefault($email, $hash) {
        try {
            $this->container->get(Emails::class)->verify($email, $hash);
            $this->flashMessage('Váš e-mail byl ověřen, můžete se přihlásit.', 'success');
        } catch (EmailAlreadyVerifiedException $e) {
            $this->flashMessage('Váš e-mail již byl v minulosti ověřen, můžete se přihlásit.', 'info');
        } catch (SecurityException $e) {
            $this->flashMessage('E-mail se nepodařilo ověřit.', 'danger');
        }
        $this->redirect(LoginPresenter::LINK_DEFAULT);
    }

}
