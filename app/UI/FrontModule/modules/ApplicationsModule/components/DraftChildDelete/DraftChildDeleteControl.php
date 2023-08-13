<?php

namespace VCD\UI\FrontModule\ApplicationsModule;

use Hafo\DI\Container;
use Hafo\NetteBridge\Forms\FormFactory;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use VCD\Users;
use VCD2\Applications\Application;
use VCD2\Applications\Child;
use VCD2\Applications\Service\Drafts;
use VCD2\Orm;
use VCD2\Users\Service\UserContext;

/**
 * @method onDelete()
 */
class DraftChildDeleteControl extends Control {

    public $onDelete = [];

    /** @var Child|NULL */
    private $child;

    function __construct(Container $container, Application $draft, $child) {

        $orm = $container->get(Orm::class);
        $user = $container->get(UserContext::class)->getEntity();
        $drafts = $container->get(Drafts::class);

        $this->child = $draft->children->get()->getBy(['id' => $child]);

        $f = $container->get(FormFactory::class)->create();
        if($user !== NULL && $this->child->child !== NULL) {
            $f->addRadioList('deleteOption', '', [
                0 => 'Odebrat pouze z přihlášky',
                1 => 'Odebrat z přihlášky a odstranit z mého profilu'
            ])->setValue(0);
        }
        $f->addSubmit('delete', 'Ano, odebrat');
        $f->addSubmit('back', 'Ne, jít zpět');
        $f->onSuccess[] = function(Form $f) use ($user, $orm, $draft, $drafts) {
            if($f->isSubmitted() === $f['delete']) {
                $data = $f->getValues(TRUE);
                if($user !== NULL && $this->child->child !== NULL && $data['deleteOption'] === 1) {
                    $orm->remove($this->child->child);
                }

                $orm->remove($this->child);
                $drafts->saveDraft($draft);
                
                $this->onDelete();
            } else if($f->isSubmitted() === $f['back']) {
                $this->onDelete();
            }
        };
        $this->addComponent($f, 'form');
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->child = $this->child;
        $this->template->render();
    }

}
