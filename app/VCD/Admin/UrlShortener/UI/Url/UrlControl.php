<?php

namespace VCD\Admin\UrlShortener\UI;

use Hafo\DI\Container;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Database\Context;
use Tomaj\Form\Renderer\BootstrapRenderer;

class UrlControl extends Control {

    function __construct(Container $c, $id = NULL) {

        $this->onAnchor[] = function () use ($c, $id) {

            $db = $c->get(Context::class);

            $f = new Form;
            $f->setRenderer(new BootstrapRenderer);
            $f->addText('path', 'včd.eu/');
            $f->addText('url', 'URL');
            $f->addProtection();

            $f->addSubmit('save', 'Uložit');
            if($id !== NULL) {
                $f->addSubmit('delete', 'Odstranit')
                    ->setValidationScope(FALSE)
                    ->getControlPrototype()
                    ->attrs['onclick'] = 'if(!confirm("Opravdu smazat?")){return false;}';
            }

            $f->onValidate[] = function(Form $f) use ($id, $db) {
                $data = $f->getValues(TRUE);
                if($id === NULL) {
                    $existing = $db->table('vcd_short_url')->where('path', $data['path'])->fetch();
                    if($existing) {
                        $f['path']->addError('Tato URL už existuje.');
                    }
                }
            };

            $f->onSuccess[] = function (Form $f) use ($id, $db) {
                if($f->isSubmitted() === $f['save']) {
                    $data = $f->getValues(TRUE);
                    if($id === NULL) {
                        $db->table('vcd_short_url')->insert($data);
                    } else {
                        $db->table('vcd_short_url')->wherePrimary($id)->update($data);
                    }
                    $this->presenter->flashMessage('Uloženo.', 'success');
                    $this->presenter->redirect('shortUrls');
                } else if($id !== NULL && $f->isSubmitted() === $f['delete']) {
                    $db->table('vcd_short_url')->wherePrimary($id)->delete();
                    $this->presenter->flashMessage('Smazáno.', 'success');
                    $this->presenter->redirect('shortUrls');
                }
            };

            if($id !== NULL) {
                $f->setValues($db->table('vcd_short_url')->wherePrimary($id)->fetch());
            }

            $this->addComponent($f, 'form');

        };

    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->render();
    }

}
