<?php

namespace VCD\UI\AuthModule;

use Hafo\NetteBridge\Forms\FormFactory;
use Hafo\Security\Storage\Passwords;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Context;
use Nette\Utils\Random;
use VCD\UI\FrontModule\HomepageModule\HomepagePresenter;
use VCD2\Emails\Service\Emails\PasswordChangeMail;

class RestorePasswordPresenter extends BasePresenter {

    const LINK_DEFAULT = ':Auth:RestorePassword:default';
    const LINK_RESTORE = ':Auth:RestorePassword:restore';

    function actionDefault() {
        if($this->user->isLoggedIn()) {
            $this->redirect(HomepagePresenter::LINK_DEFAULT);
        }
        $f = $this->container->get(FormFactory::class)->create();
        $f->addText('email', 'E-mail')->setRequired()->addRule(Form::EMAIL, 'Zadaný e-mail není ve správném tvaru.');
        $f->addSubmit('restore', 'Obnovit heslo');
        $f->onSuccess[] = function(Form $f) {
            if($f->isSubmitted() === $f['restore']) {
                $data = $f->getValues(TRUE);

                $user = $this->orm->users->getByEmail($data['email']);

                if($user) {
                    if($user->passwordRestore === NULL) {
                        $user->passwordRestore = Random::generate(40);
                        $this->orm->persistAndFlush($user);
                    }
                    $this->container->get(PasswordChangeMail::class)->send($user->id);

                    $this->flashMessage('Poslali jsme Vám e-mail s instrukcemi pro obnovu hesla.', 'success');
                    $this->redirect(LoginPresenter::LINK_DEFAULT);
                } else {
                    $this->flashMessage('Účet se zadaným e-mailem neexistuje.', 'danger');
                    $this->redirect('this');
                }
            }
        };
        $this->addComponent($f, 'form');
        $this->template->titlePrefix = 'Obnovit heslo';
    }

    function actionRestore($hash) {
        if(empty($hash)) {
            throw new ForbiddenRequestException;
        }
        $db = $this->container->get(Context::class);
        $row = $db->table('system_user')->where('password_restore', $hash)->fetch();
        if(!$row) {
            throw new ForbiddenRequestException;
        }
        $uid = $row['id'];

        $f = $this->container->get(FormFactory::class)->create();
        $f->addPassword('password', 'Heslo')->setRequired();
        $f->addPassword('password2', 'Heslo znovu')->setRequired()->addRule(Form::EQUAL, 'Zadaná hesla se neshodují.', $f['password']);
        $f->addSubmit('save', 'Nastavit');
        $f->onSuccess[] = function(Form $f) use ($uid, $db) {
            if($f->isSubmitted() === $f['save']) {
                $this->container->get(Passwords::class)->setPassword($uid, $f->getValues(TRUE)['password']);
                $this->flashMessage('Heslo bylo nastaveno.', 'success');
                $this->redirect(LoginPresenter::LINK_DEFAULT);
            }
        };
        $this->addComponent($f, 'form');
        $this->template->titlePrefix = 'Nastavit heslo';
    }

}
