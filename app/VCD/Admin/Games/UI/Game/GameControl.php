<?php

namespace VCD\Admin\Games\UI;

use Nette\Utils\Strings;
use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Database\Context;
use Nette\Http\FileUpload;
use Nette\Utils\Arrays;
use Nette\Utils\FileSystem;
use Tomaj\Form\Renderer\BootstrapRenderer;

class GameControl extends Control {

    private $container;

    function __construct(ContainerInterface $container, $id = NULL) {
        $this->container = $container;
        
        $this->onAnchor[] = function() use ($id) {
            $f = new Form;
            $f->setRenderer(new BootstrapRenderer);
            $f->addText('name', 'Název');
            if ($id !== null) {
                $f->addText('slug', 'Url slug');
            }
            $f->addCheckbox('visible', 'Viditelný');
            $f->addCheckbox('visible_on_homepage', 'Viditelný na homepage');
            $f->addUpload('banner_small', 'Obrázek na homepage');//->addCondition(Form::FILLED)->addRule(Form::IMAGE);
            $f->addUpload('banner_large', 'Obrázek na stránce Hry');//->addCondition(Form::FILLED)->addRule(Form::IMAGE);
            $f->addTextArea('description_short', 'Krátký popis na homepage');
            $f->addCKEditor('description_long', 'Popis');
            $f->addSubmit('save', 'Uložit');
            if($id !== NULL) {
                $f->addSubmit('delete', 'Odstranit')->getControlPrototype()
                    ->attrs['onclick'] = 'if(!confirm("Opravdu smazat?")){return false;}';
            }
            $f->addProtection();
            $f->onSuccess[] = function(Form $f) use ($id) {
                if($f->isSubmitted() === $f['save']) {
                    $data = $f->getValues(TRUE);

                    /**
                     * @var FileUpload|NULL $img1
                     * @var FileUpload|NULL $img2
                     * @var FileUpload|NULL $ebook
                     */
                    $img1 = Arrays::pick($data, 'banner_small');
                    $img2 = Arrays::pick($data, 'banner_large');

                    if($id === NULL) {
                        $data['position'] = $this->db()->table('vcd_game')->order('position DESC')->limit(1)->select('position')->fetchField() + 1;
                        $data['slug'] = Strings::webalize($data['name']);
                        $row = $this->db()->table('vcd_game')->insert($data);
                        $id = $row->getPrimary();
                    } else {
                        $this->db()->table('vcd_game')->wherePrimary($id)->update($data);
                    }

                    $dir = $this->container->get('games') . '/' . $id;
                    $update = [];
                    FileSystem::createDir($dir);
                    if($img1 !== NULL && $img1->isOk()) {
                        $imgFile = $dir . '/' . $img1->getSanitizedName();
                        $img1->move($imgFile);
                        $update['banner_small'] = str_replace($this->container->get('www'), '', $imgFile);
                    }
                    if($img2 !== NULL && $img2->isOk()) {
                        $imgFile = $dir . '/' . $img2->getSanitizedName();
                        $img2->move($imgFile);
                        $update['banner_large'] = str_replace($this->container->get('www'), '', $imgFile);
                    }
                    if(!empty($update)) {
                        $this->db()->table('vcd_game')->wherePrimary($id)->update($update);
                    }
                    $this->presenter->flashMessage('Uloženo.', 'success');
                    $this->presenter->redirect('games');
                } else if($id !== NULL && $f->isSubmitted() === $f['delete']) {
                    $this->db()->table('vcd_game')->wherePrimary($id)->delete();
                    $this->presenter->flashMessage('Smazáno.', 'success');
                    $this->presenter->redirect('games');
                }
            };
            if($id !== NULL) {
                $f->setValues($this->db()->table('vcd_game')->wherePrimary($id)->fetch());
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
