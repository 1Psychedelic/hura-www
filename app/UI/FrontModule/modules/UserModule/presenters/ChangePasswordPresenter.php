<?php

namespace VCD\UI\FrontModule\UserModule;

use Hafo\NetteBridge\Forms\FormFactory;
use Hafo\Security\Storage\Passwords;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Context;
use VCD\UI\AuthModule\LoginPresenter;

class ChangePasswordPresenter extends BasePresenter {

    const LINK_DEFAULT = ':Front:User:ChangePassword:default';

    function actionDefault() {
        if(!$this->user->isLoggedIn()) {
            $this->redirect(LoginPresenter::LINK_DEFAULT, ['go' => $this->link('this')]);
        }
        $db = $this->container->get(Context::class);
        $row = $db->table('system_user')->where('id = ? AND password IS NOT NULL', $this->user->id)->fetch();
        if(!$row) {
            throw new ForbiddenRequestException;
        }

        $f = $this->container->get(FormFactory::class)->create();
        $f->addPassword('current', 'Aktuální heslo')->setRequired();
        $f->addPassword('password', 'Nové heslo')->setRequired();
        $f->addPassword('password2', 'Nové heslo znovu')->setRequired()->addRule(Form::EQUAL, 'Zadaná hesla se neshodují.', $f['password']);
        $f->addSubmit('save', 'Nastavit');
        $f->onSuccess[] = function(Form $f) {
            if($f->isSubmitted() === $f['save']) {
                $data = $f->getValues(TRUE);
                $passwords = $this->container->get(Passwords::class);
                $uid = $passwords->verifyPassword($this->user->getIdentity()->data['email'], $data['current']);
                if($uid !== $this->user->getId()) {
                    $this->flashMessage('Nesprávné heslo.', 'danger');
                    $this->redirect('this');
                    return;
                }
                $passwords->setPassword($this->user->id, $data['password']);
                $this->flashMessage('Heslo bylo změněno.', 'success');
                $this->redirect(ProfilePresenter::LINK_DEFAULT);
            }
        };
        $this->addComponent($f, 'form');
        $this->template->titlePrefix = 'Změnit heslo';
    }

}
