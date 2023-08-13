<?php

namespace VCD\Admin\Leaders\UI;

use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Database\Context;
use Tomaj\Form\Renderer\BootstrapRenderer;

class LeaderControl extends Control {

    private $container;

    function __construct(ContainerInterface $container, $id = NULL) {
        $this->container = $container;

        $this->onAnchor[] = function() use ($id) {
            $f = new Form;
            $f->setRenderer(new BootstrapRenderer);
            $f->addText('name', 'Jméno');
            $f->addCheckbox('visible', 'Viditelný');
            $f->addCKEditor('info', 'Info pod fotkou');
            $f->addCKEditor('about', 'Text');
            $f->addUpload('photo', 'Fotka');//->addCondition(Form::FILLED)->addRule(Form::IMAGE);
            $f->addProtection();
            $f->addSubmit('save', 'Uložit');
            if($id !== NULL) {
                $f->addSubmit('delete', 'Smazat')->getControlPrototype()
                    ->attrs['onclick'] = 'if(!confirm("Opravdu smazat?")){return false;}';
            }
            $f->onSuccess[] = function(Form $f) use ($id) {
                if($f->isSubmitted() === $f['save']) {
                    $data = $f->getValues(TRUE);
                    $dir = $this->container->get('leaders') . '/' . $id;
                    if($data['photo']->isOk()) {
                        $filename = $data['photo']->getSanitizedName();
                        $data['photo']->move($dir . '/' . $filename);
                        $data['photo'] = str_replace($this->container->get('www'), '', $dir . '/' . $filename);
                    } else {
                        unset($data['photo']);
                    }
                    if($id === NULL) {
                        $data['position'] = $this->db()->table('vcd_leader')->order('position DESC')->limit(1)->select('position')->fetchField() + 1;
                        $this->db()->table('vcd_leader')->insert($data);
                    } else {
                        $this->db()->table('vcd_leader')->wherePrimary($id)->update($data);
                    }
                    $this->presenter->flashMessage('Uloženo.', 'success');
                    $this->presenter->redirect('leaders');
                } else if($id !== NULL && $f->isSubmitted() === $f['delete']) {
                    $this->db()->table('vcd_leader')->wherePrimary($id)->delete();
                    $this->presenter->flashMessage('Smazáno.', 'success');
                    $this->presenter->redirect('leaders');
                }
            };
            if($id !== NULL) {
                $f->setValues($this->db()->table('vcd_leader')->wherePrimary($id)->fetch());
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
