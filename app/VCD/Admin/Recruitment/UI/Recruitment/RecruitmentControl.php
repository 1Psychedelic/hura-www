<?php

namespace VCD\Admin\Recruitment\UI;

use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Database\Context;
use Nette\Database\UniqueConstraintViolationException;
use Nette\Http\FileUpload;
use Nette\Utils\Arrays;
use Nette\Utils\FileSystem;
use Nette\Utils\Strings;
use Tomaj\Form\Renderer\BootstrapRenderer;

class RecruitmentControl extends Control {

    private $container;

    function __construct(ContainerInterface $container, $id = NULL) {
        $this->container = $container;
        
        $this->onAnchor[] = function() use ($id) {
            $f = new Form;
            $f->setRenderer(new BootstrapRenderer);
            $f->addText('title', 'Název');
            $f->addText('google_forms_link', 'Odkaz na Google formulář');
            $f->addCheckbox('visible', 'Viditelné');
            $f->addUpload('image_upload', 'Obrázek');//->addCondition(Form::FILLED)->addRule(Form::IMAGE);
            $f->addCKEditor('description', 'Popis');
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
                     * @var FileUpload|NULL $img
                     */
                    $img = Arrays::pick($data, 'image_upload');

                    $error = 0;
                    do {
                        try {
                            $data['slug'] = Strings::webalize($data['title']) . ($error !== 0 ? '-' . $error : '');
                            if($id === NULL) {
                                $data['position'] = $this->db()->table('vcd_recruitment')->order('position DESC')->limit(1)->select('position')->fetchField() + 1;
                                $row = $this->db()->table('vcd_recruitment')->insert($data);
                                $id = $row->getPrimary();
                            } else {
                                $this->db()->table('vcd_recruitment')->wherePrimary($id)->update($data);
                            }
                        } catch (UniqueConstraintViolationException $e) {
                            $error++;
                        }
                    } while ($error !== 0);

                    $dir = $this->container->get('recruitment') . '/' . $id;
                    $update = [];
                    FileSystem::createDir($dir);
                    if($img !== NULL && $img->isOk()) {
                        $imgFile = $dir . '/' . $img->getSanitizedName();
                        $img->move($imgFile);
                        $update['image'] = str_replace($this->container->get('www'), '', $imgFile);
                    }
                    if(!empty($update)) {
                        $this->db()->table('vcd_recruitment')->wherePrimary($id)->update($update);
                    }
                    $this->presenter->flashMessage('Uloženo.', 'success');
                    $this->presenter->redirect('recruitments');
                } else if($id !== NULL && $f->isSubmitted() === $f['delete']) {
                    $this->db()->table('vcd_recruitment')->wherePrimary($id)->delete();
                    $this->presenter->flashMessage('Smazáno.', 'success');
                    $this->presenter->redirect('recruitments');
                }
            };
            if($id !== NULL) {
                $f->setValues($this->db()->table('vcd_recruitment')->wherePrimary($id)->fetch());
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
