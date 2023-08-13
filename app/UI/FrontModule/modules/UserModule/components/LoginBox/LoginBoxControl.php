<?php

namespace VCD\UI\FrontModule\UserModule;

use Hafo\NetteBridge\Forms\FormFactory;
use Hafo\Security\Authentication\Authenticator\PasswordLogin;
use Hafo\Security\Authentication\EmailNotVerifiedException;
use Hafo\Security\Authentication\LoginException;
use Hafo\Security\SecurityException;
use Hafo\Security\Storage\Emails;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Security\User;
use Hafo\Facebook\UI\FacebookLoginComponent;
use Hafo\Google\UI\GoogleLoginComponent;
use VCD\UI\AuthModule\LoginPresenter;
use VCD2\Emails\Service\Emails\EmailVerifyMail;

class LoginBoxControl extends Control {

    private $user;

    private $wrapper = TRUE;

    private $redirectUrl;

    function __construct(User $user,
                         FormFactory $formFactory,
                         FacebookLoginComponent $facebook,
                         GoogleLoginComponent $google,
                         PasswordLogin $passwordLogin,
                         Emails $emails,
                         EmailVerifyMail $verifyMail) {
        $this->user = $user;

        $facebook->onAuthorize[] = function() {
            $this->presenter->flashMessage('Přihlášení bylo úspěšné.', 'success');
            if($this->redirectUrl === NULL) {
                $this->presenter->realRedirect('this');
            } else {
                $this->presenter->realRedirectUrl($this->redirectUrl);
            }
        };
        $facebook->onDeauthorize[] = function() {
            $this->user->logout(TRUE);
            $this->presenter->realRedirect('this');
        };
        $facebook->onError[] = function() {
            $this->presenter->flashMessage('Přihlášení přes Facebook selhalo. Zkuste to znovu později nebo vyberte jiný způsob přihlášení.', 'danger');
            $this->presenter->realRedirect('this');
        };
        $this->addComponent($facebook, 'fb');

        $google->onAuthorize[] = function() {
            $this->presenter->flashMessage('Přihlášení bylo úspěšné.', 'success');
            if($this->redirectUrl === NULL) {
                $this->presenter->realRedirect('this');
            } else {
                $this->presenter->redirectUrl($this->redirectUrl);
            }
        };
        $google->onError[] = function() {
            $this->presenter->flashMessage('Přihlášení přes Google selhalo. Zkuste to znovu později nebo vyberte jiný způsob přihlášení.', 'danger');
            $this->presenter->realRedirect('this');
        };
        $this->addComponent($google, 'google');

        $f = $formFactory->create();
        $f->addText('email', 'E-mail')->setRequired()->addRule(Form::EMAIL);
        $f->addPassword('password', 'Heslo')->setRequired();
        $f->addSubmit('login', 'Přihlásit se');
        $f->onSuccess[] = function(Form $f) use ($passwordLogin, $emails, $verifyMail) {
            if($f->isSubmitted() === $f['login']) {
                $data = $f->getValues(TRUE);
                try {
                    $passwordLogin->login($data);
                    $this->presenter->flashMessage('Přihlášení bylo úspěšné.', 'success');
                    if($this->redirectUrl === NULL) {
                        $this->presenter->realRedirect('this');
                    } else {
                        $this->presenter->realRedirectUrl($this->redirectUrl);
                    }
                } catch (EmailNotVerifiedException $e) {
                    if($id = $e->getUserId()) {
                        $this->presenter->flashMessage('Vaše e-mailová adresa nebyla ověřena. Klikněte prosím na odkaz, který jsme Vám poslali.', 'danger');
                        $verifyMail->send($data['email'], $emails->requestEmailVerifyHash($data['email']));
                    } else {
                        $this->presenter->flashMessage('Nesprávné přihlašovací údaje.', 'danger');
                    }
                    $this->presenter->realRedirect(LoginPresenter::LINK_DEFAULT, ['go' => $this->redirectUrl]);
                } catch (LoginException $e) {
                    $this->presenter->flashMessage('Nesprávné přihlašovací údaje.', 'danger');
                    $this->presenter->redirect(LoginPresenter::LINK_DEFAULT, ['go' => $this->redirectUrl]);
                } catch (SecurityException $e) {
                    $this->presenter->flashMessage('Během pokusu o přihlášení došlo k chybě.', 'danger');
                    $this->presenter->realRedirect(LoginPresenter::LINK_DEFAULT, ['go' => $this->redirectUrl]);
                }
            }
        };
        $this->addComponent($f, 'loginForm');
    }

    function setRedirectUrl($url) {
        $this->redirectUrl = $url;
        return $this;
    }

    function noWrapper() {
        $this->wrapper = FALSE;
        return $this;
    }

    function facebookAuthorizeUrl() {
        return $this->presenter->link('this', ['do' => $this['fb']->getParameterId('authorize')]);
    }

    function facebookDeauthorizeUrl() {
        return $this->presenter->link('this', ['do' => $this['fb']->getParameterId('deauthorize')]);
    }

    function googleAuthorizeUrl() {
        return $this->presenter->link('this', ['do' => $this['google']->getParameterId('authorize')]);
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->wrapper = $this->wrapper;
        $this->template->facebookAuthorize = $this->facebookAuthorizeUrl();
        $this->template->facebookDeauthorize = $this->facebookDeauthorizeUrl();
        $this->template->googleAuthorize = $this->googleAuthorizeUrl();
        $this->template->render();
    }

}
