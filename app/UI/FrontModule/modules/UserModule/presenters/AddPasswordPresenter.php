<?php

namespace VCD\UI\FrontModule\UserModule;

use Hafo\NetteBridge\Forms\FormFactory;
use Hafo\Security\Storage\Passwords;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Context;

class AddPasswordPresenter extends BasePresenter {

    const LINK_DEFAULT = ':Front:User:AddPassword:default';

    function actionDefault() {
        $db = $this->container->get(Context::class);
        $row = $db->table('system_user')->where('id = ? AND password IS NULL', $this->user->id)->fetch();
        if(!$row) {
            throw new ForbiddenRequestException;
        }
        $uid = $this->user->id;

        $f = $this->container->get(FormFactory::class)->create();
        $f->addPassword('password', 'Heslo')->setRequired();
        $f->addPassword('password2', 'Heslo znovu')->setRequired()->addRule(Form::EQUAL, 'Zadaná hesla se neshodují.', $f['password']);
        $f->addSubmit('save', 'Nastavit');
        $f->onSuccess[] = function(Form $f) use ($uid) {
            if($f->isSubmitted() === $f['save']) {
                $this->container->get(Passwords::class)->setPassword($uid, $f->getValues(TRUE)['password']);
                $this->flashMessage('Heslo bylo nastaveno.', 'success');
                $this->redirect(ProfilePresenter::LINK_DEFAULT);
            }
        };
        $this->addComponent($f, 'form');
        $this->template->titlePrefix = 'Nastavit heslo';
    }

}
