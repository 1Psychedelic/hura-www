<?php

namespace VCD\Admin\Events\UI;

use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Database\ConstraintViolationException;
use Nette\Database\Context;
use Nette\Utils\Strings;
use Tomaj\Form\Renderer\BootstrapRenderer;

class EventStepOptionControl extends Control {

    private $container;

    function __construct(ContainerInterface $container, $event, $step, $id = NULL) {
        $this->container = $container;
        
        $this->onAnchor[] = function() use ($event, $step, $id) {
            $f = new Form;
            $f->setRenderer(new BootstrapRenderer);
            $f->addText('option', 'Text volby');
            $f->addText('max_usages', 'Kapacita')->setNullable()->setAttribute('placeholder', 'Neomezeno');
            $f->addText('price', 'Cena');
            $f->addRadioList('absolute_price', 'Metoda', [
                0 => 'Uvedená cena je relativní (příplatek/sleva)',
                1 => 'Uvedená cena je finální',
            ]);
            $f->addCheckbox('multiply_by_children', 'Vynásobit cenu počtem dětí');
            $f->addCheckbox('allow_sibling_discount', 'Povolit sourozeneckou slevu');
            $f->addCheckbox('allow_discount_codes', 'Povolit slevové kódy');
            $f->addProtection();
            $f->addSubmit('save', 'Uložit');
            if($id !== NULL) {
                $f->addSubmit('delete', 'Smazat')->getControlPrototype()
                    ->attrs['onclick'] = 'if(!confirm("Opravdu smazat?")){return false;}';
            }
            $f->onSuccess[] = function(Form $f) use ($event, $step, $id) {
                if($f->isSubmitted() === $f['save']) {
                    $data = $f->getValues(TRUE);
                    if($id === NULL) {
                        $data['step'] = $step;
                        $data['position'] = $this->db()->table('vcd_event_step_option')->where('step', $step)->select('MAX(position)')->fetchField() + 1;
                        $this->db()->table('vcd_event_step_option')->insert($data);
                    } else {
                        $this->db()->table('vcd_event_step_option')->wherePrimary($id)->update($data);
                    }
                    $this->presenter->flashMessage('Uloženo.', 'success');
                    $this->presenter->redirect('eventStepOptions', ['event' => $event, 'step' => $step]);
                } else if($id !== NULL && $f->isSubmitted() === $f['delete']) {
                    try {
                        $this->db()->table('vcd_event_step_option')->wherePrimary($id)->delete();
                        $this->presenter->flashMessage('Smazáno.', 'success');
                    } catch(ConstraintViolationException $e) {
                        $this->presenter->flashMessage('Tuto položku nejde smazat, nejspíš k ní jsou již navázané nějaké přihlášky.', 'danger');
                    }
                    $this->presenter->redirect('eventStepOptions', ['event' => $event, 'step' => $step]);
                }
            };
            if($id !== NULL) {
                $f->setValues($this->db()->table('vcd_event_step_option')->wherePrimary($id)->fetch());
            }
            $this->addComponent($f, 'form');
            $this->template->event = $event;
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
