<?php

namespace VCD\Admin\NameDays\UI;

use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Database\Context;
use Tomaj\Form\Renderer\BootstrapRenderer;

class NameDayControl extends Control {

    private $container;

    function __construct(ContainerInterface $container, $id = NULL) {
        $this->container = $container;

        $this->onAnchor[] = function() use ($id) {
            $months = range(1, 12);
            $days = range(1, 31);
            $f = new Form;
            $f->setRenderer(new BootstrapRenderer);
            $f->addText('name', 'Jméno');
            $f->addSelect('month', 'Měsíc', array_combine($months, $months));
            $f->addSelect('day', 'Den', array_combine($days, $days));
            $f->addProtection();
            $f->addSubmit('save', 'Uložit');
            if($id !== NULL) {
                $f->addSubmit('delete', 'Smazat')->getControlPrototype()
                    ->attrs['onclick'] = 'if(!confirm("Opravdu smazat?")){return false;}';
            }
            $f->onSuccess[] = function(Form $f) use ($id) {
                if($f->isSubmitted() === $f['save']) {
                    $data = $f->getValues(TRUE);
                    if($id === NULL) {
                        $this->db()->table('name_days')->insert($data);
                    } else {
                        $this->db()->table('name_days')->wherePrimary($id)->update($data);
                    }
                    $this->presenter->flashMessage('Uloženo.', 'success');
                    $this->presenter->redirect('nameDays');
                } else if($id !== NULL && $f->isSubmitted() === $f['delete']) {
                    $this->db()->table('name_days')->wherePrimary($id)->delete();
                    $this->presenter->flashMessage('Smazáno.', 'success');
                    $this->presenter->redirect('nameDays');
                }
            };
            if($id !== NULL) {
                $f->setValues($this->db()->table('name_days')->wherePrimary($id)->fetch());
            }
            $this->addComponent($f, 'form');
        };
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->render();
    }

    private function db() {
        return $this->container->get(Context::class);
    }

}
