<?php

namespace VCD\UI\FrontModule\UserModule;

use Hafo\DI\Container;
use Hafo\NetteBridge\Forms\FormFactory;
use Hafo\Security\Storage\Profiles;
use Psr\Container\ContainerInterface;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Security\User;
use Tomaj\Form\Renderer\BootstrapRenderer;
use VCD2\Orm;
use VCD2\Users\Service\UserContext;

class ProfileParentControl extends Control
{

    function __construct(Container $container)
    {

        $orm = $container->get(Orm::class);
        $user = $container->get(UserContext::class)->getEntity();
        if ($user === NULL) {
            throw new ForbiddenRequestException;
        }

        $f = $container->get(FormFactory::class)->create();
        $f->setRenderer(new BootstrapRenderer);
        //$f->addText('name', 'Jméno a příjmení')->setRequired()->addRule(Form::PATTERN, 'Zadejte prosím své jméno a příjmení', '(.*)\s(.*)');
        //$f->addText('email', 'E-mail')->setRequired()->addRule(Form::EMAIL);
        //$f->addText('phone', 'Telefon');
        $f->addText('city', 'Město');
        $f->addText('street', 'Ulice a číslo domu');
        $f->addText('zip', 'PSČ');
        $f->addSubmit('send', 'Uložit');
        $f->addSubmit('back', 'Jít zpět')->setValidationScope(FALSE);
        $f->setValues($user->getValues(['city', 'street', 'zip']));
        $f->onSuccess[] = function (Form $f) use ($orm, $user) {
            if ($f->isSubmitted() === $f['send']) {
                $data = $f->getValues(TRUE);
                $user->updateInfo($user->name, $user->phone, $data['city'], $data['street'], $data['zip']);
                $orm->persistAndFlush($user);
                $this->presenter->flashMessage('Profil byl uložen.', 'success');
                $this->presenter->redirect(ProfilePresenter::LINK_DEFAULT);
            } else if ($f->isSubmitted() === $f['back']) {
                $this->presenter->redirect(ProfilePresenter::LINK_DEFAULT);
            }
        };
        $this->addComponent($f, 'form');
    }

    function render()
    {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->render();
    }

}
