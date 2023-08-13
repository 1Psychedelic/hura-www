<?php

namespace VCD\UI\AuthModule;

use Hafo\NetteBridge\Forms\FormFactory;
use Hafo\Security\Authentication\IdAuthenticator;
use Hafo\Security\Storage\Emails;
use Hafo\Security\Storage\Passwords;
use Hafo\Security\Storage\Users;
use Nette\Application\ForbiddenRequestException;
use Nette\Forms\Form;
use Nette\Utils\Html;
use VCD\UI\FrontModule\HomepageModule\HomepagePresenter;

class CompleteSignupPresenter extends BasePresenter {

    const LINK_DEFAULT = ':Auth:CompleteSignup:default';

    function actionDefault() {
        if ($this->user->isLoggedIn() && $this->userContext->getEntity()->canLogin) {
            $this->redirect(HomepagePresenter::LINK_DEFAULT);
        }

        if(!$this->user->isLoggedIn() && !$this->isSignalReceiver('', 'login')) {
            throw new ForbiddenRequestException;
        }

        $f = $this->container->get(FormFactory::class)->create();
        $f->addPassword('password', 'Heslo')->setRequired();
        $f->addPassword('password2', 'Heslo znovu')->setRequired()->addRule(Form::EQUAL, 'Zadaná hesla se neshodují.', $f['password']);
        $f->addSubmit('save', 'Nastavit heslo a aktivovat účet');
        $f->onSuccess[] = function(Form $f) {
            if($f->isSubmitted() === $f['save']) {
                $this->container->get(Passwords::class)->setPassword($this->user->id, $f->getValues(TRUE)['password']);
                $this->flashMessage('Heslo bylo nastaveno.', 'success');
                $this->redirect(LoginPresenter::LINK_DEFAULT);
            }
        };
        $this->addComponent($f, 'form');
        $this->template->titlePrefix = 'Aktivovat účet';

        $this->template->facebookAuthorize = $this->presenter->link('this', ['do' => $this['user-fb']->getParameterId('authorize')]);
        $this->template->facebookDeauthorize = $this->presenter->link('this', ['do' => $this['user-fb']->getParameterId('deauthorize')]);
        $this->template->googleAuthorize = $this->presenter->link('this', ['do' => $this['user-google']->getParameterId('authorize')]);
    }

    function handleLogin($hash) {
        $user = $this->userContext->getEntity();
        if(empty($hash) || ($user !== NULL && $user->loginHash !== $hash)) {
            throw new ForbiddenRequestException;
        }

        if($user === NULL) {
            $user = $this->orm->users->getBy(['loginHash' => $hash]);
            if ($user === NULL) {
                throw new ForbiddenRequestException;
            } elseif ($user->canLogin) {
                $this->flashMessage(
                    Html::el()->setHtml(
                        sprintf(
                            'Vámi použitý jednorázový odkaz pro aktivaci je již neplatný, Váš účet byl v minulosti aktivován.
                            <br>Pro pokračování do Vašeho účtu se prosím přihlaste, nebo si můžete <a href="%s">obnovit zapomenuté heslo</a>.',
                            $this->link(RestorePasswordPresenter::LINK_DEFAULT)
                        )
                    ),
                    'danger'
                );
                $this->redirect(LoginPresenter::LINK_DEFAULT);
            }
            $this->container->get(IdAuthenticator::class)->login($user->id);
        }

        $this->redirect(self::LINK_DEFAULT);
    }

}
