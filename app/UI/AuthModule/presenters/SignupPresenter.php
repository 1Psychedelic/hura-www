<?php

namespace VCD\UI\AuthModule;

use Hafo\NetteBridge\Forms\FormFactory;
use Hafo\Security\Storage\Emails;
use Hafo\Security\Storage\Passwords;
use Hafo\Security\Storage\Users;
use Nette\Forms\Form;
use VCD\UI\FrontModule\HomepageModule\HomepagePresenter;
use VCD2\Emails\Service\Emails\EmailVerifyMail;

class SignupPresenter extends BasePresenter {

    const LINK_DEFAULT = ':Auth:Signup:default';

    function actionDefault() {
        if($this->user->isLoggedIn()) {
            $this->redirect(HomepagePresenter::LINK_DEFAULT);
        }
        $f = $this->container->get(FormFactory::class)->create();
        $f->addText('name', 'Jméno a příjmení')->setRequired();
        $f->addText('email', 'E-mail')->setRequired()->addRule(Form::EMAIL, 'Zadaný e-mail není ve správném tvaru.');
        $f->addPassword('password', 'Heslo')->setRequired();
        $f->addPassword('password2', 'Heslo znovu')->setRequired()->addRule(Form::EQUAL, 'Zadaná hesla se neshodují.', $f['password']);
        $f->addSubmit('signup', 'Zaregistrovat se');
        $f->onSuccess[] = function(Form $f) {
            if($f->isSubmitted() === $f['signup']) {
                $data = $f->getValues(TRUE);
                if($id = $this->container->get(Users::class)->exists($data['email'], 'email')) {
                    $this->flashMessage('Účet s touto e-mailovou adresou je již registrován.', 'danger');
                    $this->redirect('this');
                    return;
                }
                $userId = $this->container->get(Users::class)->register($data['email'], ['name' => $data['name']]);
                $this->container->get(Passwords::class)->setPassword($userId, $data['password']);
                $hash = $this->container->get(Emails::class)->requestEmailVerifyHash($data['email']);
                $this->container->get(EmailVerifyMail::class)->send($data['email'], $hash);
                $this->presenter->flashMessage('Účet byl vytvořen. Na e-mail jsme Vám poslali odkaz, na který je potřeba kliknout, než se budete moci přihlásit.', 'success');
                $this->redirect(LoginPresenter::LINK_DEFAULT);
            }
        };
        $this->addComponent($f, 'form');
        $this->template->titlePrefix = 'Registrace';
    }

}
