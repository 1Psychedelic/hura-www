<?php

namespace VCD\Admin\Website\UI;

use HuraTabory\Domain\Website\CustomJavascript;
use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Database\Context;
use Tomaj\Form\Renderer\BootstrapRenderer;

class CodeControl extends Control {

    static public $visibility = [
        CustomJavascript::VISIBILITY_DISABLED_FOR_ALL => 'Vypnuto pro všechny',
        CustomJavascript::VISIBILITY_ENABLED_FOR_ALL => 'Zapnuto pro všechny',
        CustomJavascript::VISIBILITY_ENABLED_FOR_ALL_BUT_ADMIN => 'Zapnuto pro všechny kromě administrátorů',
        CustomJavascript::VISIBILITY_ENABLED_FOR_GUESTS_ONLY => 'Zapnuto pouze pro nepřihlášené',
        //4 => 'Zapnuto pro všechny kromě administrátorů, vypnuto v sekcích blog a ebooky'
    ];

    private $container;

    function __construct(ContainerInterface $container, $id = NULL) {
        $this->container = $container;

        $this->onAnchor[] = function() use ($id) {
            $f = new Form;
            $f->setRenderer(new BootstrapRenderer);
            $f->addText('name', 'Název');
            $f->addRadioList('visible', 'Omezení', self::$visibility);
            $f->addTextArea('code', 'Kód');
            $f->addSubmit('save', 'Uložit');
            if($id !== NULL) {
                $f->addSubmit('delete', 'Smazat')->getControlPrototype()
                    ->attrs['onclick'] = 'if(!confirm("Opravdu smazat?")){return false;}';
            }
            $f->onSuccess[] = function(Form $f) use ($id) {
                if($f->isSubmitted() === $f['save']) {
                    $data = $f->getValues(TRUE);
                    if($id === NULL) {
                        $data['position'] = $this->db()->table('vcd_web_code')->order('position DESC')->limit(1)->select('position')->fetchField() + 1;
                        $this->db()->table('vcd_web_code')->insert($data);
                    } else {
                        $this->db()->table('vcd_web_code')->wherePrimary($id)->update($data);
                    }
                    $this->presenter->flashMessage('Uloženo.', 'success');
                    $this->presenter->redirect('codes');
                } else if($id !== NULL && $f->isSubmitted() === $f['delete']) {
                    $this->db()->table('vcd_web_code')->wherePrimary($id)->delete();
                    $this->presenter->flashMessage('Smazáno.', 'success');
                    $this->presenter->redirect('codes');
                }
            };
            if($id !== NULL) {
                $f->setValues($this->db()->table('vcd_web_code')->wherePrimary($id)->fetch());
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
