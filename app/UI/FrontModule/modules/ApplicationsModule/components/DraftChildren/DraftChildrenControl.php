<?php

namespace VCD\UI\FrontModule\ApplicationsModule;

use Hafo\DI\Container;
use Hafo\Persona\Gender;
use Hafo\Persona\HumanAge;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Control;
use VCD2\Applications;
use VCD2\Applications\Application;
use VCD2\Applications\Child;
use VCD2\FlashMessageException;
use VCD2\Orm;
use VCD2\Users\Child as UserChild;
use VCD2\Users\Service\UserContext;
use VCD2\Users\User;

class DraftChildrenControl extends Control {

    /** @var Orm */
    private $orm;
    
    /** @var NULL|User */
    private $user;

    /** @var Application */
    private $draft;

    function __construct(Container $container, Application $draft) {
        $this->orm = $container->get(Orm::class);
        $this->user = $container->get(UserContext::class)->getEntity();
        $this->draft = $draft;
    }

    function handleSave($child) { // uložit do profilu
        if($this->user === NULL) {
            throw new ForbiddenRequestException;
        }
        
        /** @var Child|NULL $applicationChild */
        $applicationChild = $this->draft->children->get()->getBy(['id' => $child]);
        if($applicationChild === NULL || $applicationChild->child !== NULL) {
            throw new ForbiddenRequestException;
        }
        
        $child = UserChild::createFromApplicationChild($applicationChild);
        $this->orm->persist($child);
        $applicationChild->child = $child;
        $this->orm->persist($applicationChild);
        $this->draft->recalculatePrice();
        $this->orm->persist($this->draft);
        $this->orm->flush();
        
        $this->redirect('this#tabs');
    }

    function handleAdd($child) { // přidat do přihlášky
        if($this->user === NULL) {
            throw new ForbiddenRequestException;
        }

        /** @var UserChild|NULL $userChild */
        $userChild = $this->user->children->get()->getBy(['id' => $child]);
        if($userChild === NULL) {
            throw new ForbiddenRequestException;
        }

        try {
            $applicationChild = Child::createFromUserChild($this->draft, $userChild);
            $this->draft->recalculatePrice();
            $this->orm->persist($this->draft);
            $this->orm->persistAndFlush($applicationChild);
            $femaleSuffix = $applicationChild->gender === Gender::FEMALE ? 'a' : '';
            $this->presenter->flashMessage(sprintf('%s byl%s přidán%s do přihlášky. Zkontrolujte prosím, zda jsou všechny uložené informace stále aktuální.', $applicationChild->name, $femaleSuffix, $femaleSuffix), 'info');

            // doporučený věk
            if(!$applicationChild->isWithinRecommendedAge) {
                $age = (new HumanAge($applicationChild->dateBorn))->yearsAt($this->draft->event->ends);
                $this->parent->flashMessage(sprintf(
                    'Věk vašeho dítěte %s je vyšší než doporučený věk pro tuto akci. Přihlášku můžete odeslat, upozorňujeme však, že akce je určena primárně mladším dětem.',
                    $age
                ), 'warning');
            }

            $this->presenter->redirect('this');
        } catch (FlashMessageException $e) {
            $this->presenter->flashMessage($e->getFlashMessage());
            $this->presenter->redirect('this#tabs');
        }
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->draft = $this->draft;
        $this->template->children = $this->draft->children;

        if($this->user !== NULL) {
            $this->template->myChildren = $this->user->children;
            $this->template->isApplying = function(UserChild $userChild) {
                foreach($this->draft->children as $child) {
                    if($child->child !== NULL && $child->child === $userChild) {
                        return TRUE;
                    }
                }
                return FALSE;
            };
        }
        $this->template->render();
    }

}
