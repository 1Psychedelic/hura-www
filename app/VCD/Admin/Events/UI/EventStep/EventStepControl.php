<?php

namespace VCD\Admin\Events\UI;

use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Database\ConstraintViolationException;
use Nette\Database\Context;
use Nette\Utils\Strings;
use Tomaj\Form\Renderer\BootstrapRenderer;

class EventStepControl extends Control {

    private $container;

    function __construct(ContainerInterface $container, $event, $id = NULL) {
        $this->container = $container;
        
        $this->onAnchor[] = function() use ($event, $id) {
            $f = new Form;
            $f->setRenderer(new BootstrapRenderer);
            $f->addText('tab', 'Nadpis záložky');
            $f->addText('prompt', 'Výzva/otázka');
            //$f->addCheckbox('multiple', 'Použít hranatá zaškrtávátka');
            //$f->addCheckbox('required', 'Vyžadovat vyplnění kroku');
            $f->addProtection();
            $f->addSubmit('save', 'Uložit');
            if($id !== NULL) {
                $f->addSubmit('delete', 'Smazat')->getControlPrototype()
                    ->attrs['onclick'] = 'if(!confirm("Opravdu smazat?")){return false;}';
            }
            $f->onSuccess[] = function(Form $f) use ($event, $id) {
                if($f->isSubmitted() === $f['save']) {
                    $data = $f->getValues(TRUE);
                    $data['required'] = TRUE;
                    $error = 0;
                    do {
                        $slug = Strings::webalize($data['tab']) . ($error !== 0 ? '-' . $error : '');
                        $slugSelection = $this->db()->table('vcd_event_step')->where('event = ? AND slug = ?', [$event, $slug]);
                        if($id !== NULL) {
                            $slugSelection->where('id != ?', $id);
                        }
                        $row = $slugSelection->fetch();
                        if(!$row) {
                            $data['slug'] = $slug;
                            if($id === NULL) {
                                $data['event'] = $event;
                                $data['position'] = $this->db()->table('vcd_event_step')->where('event', $event)->select('MAX(position)')->fetchField() + 1;
                                $this->db()->table('vcd_event_step')->insert($data);
                            } else {
                                $this->db()->table('vcd_event_step')->wherePrimary($id)->update($data);
                            }
                        }
                    } while($row);
                    $this->presenter->flashMessage('Uloženo.', 'success');
                    $this->presenter->redirect('eventSteps', ['event' => $event]);
                } else if($id !== NULL && $f->isSubmitted() === $f['delete']) {
                    try {
                        $this->db()->table('vcd_event_step')->wherePrimary($id)->delete();
                        $this->presenter->flashMessage('Smazáno.', 'success');
                    } catch(ConstraintViolationException $e) {
                        $this->presenter->flashMessage('Tuto položku nejde smazat, nejspíš k ní jsou již navázané nějaké přihlášky.', 'danger');
                    }
                    $this->presenter->redirect('eventSteps', ['event' => $event]);
                }
            };
            if($id !== NULL) {
                $f->setValues($this->db()->table('vcd_event_step')->wherePrimary($id)->fetch());
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
