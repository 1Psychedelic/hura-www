<?php

namespace VCD\Admin\Ebooks\UI;

use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Database\Context;
use Nette\Http\FileUpload;
use Nette\Utils\Arrays;
use Nette\Utils\FileSystem;
use Tomaj\Form\Renderer\BootstrapRenderer;

class EbookControl extends Control {

    private $container;

    function __construct(ContainerInterface $container, $id = NULL) {
        $this->container = $container;
        
        $this->onAnchor[] = function() use ($id) {

            $events = $this->db()->table('vcd_event')->order('id DESC')->fetchPairs('id', 'name');

            $f = new Form;
            $f->setRenderer(new BootstrapRenderer);
            $f->addText('name', 'Název');
            $f->addXSelect('event', 'Událost', $events)->setPrompt('(Nepřiřazeno)');
            $f->addText('downloaded', 'Počet stažení');
            $f->addCheckbox('visible', 'Viditelný');
            $f->addUpload('image_upload', 'Obrázek');//->addCondition(Form::FILLED)->addRule(Form::IMAGE);
            $f->addUpload('ebook_upload', 'E-book');
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
                     * @var FileUpload|NULL $ebook
                     */
                    $img = Arrays::pick($data, 'image_upload');
                    $ebook = Arrays::pick($data, 'ebook_upload');

                    if($id === NULL) {
                        $data['position'] = $this->db()->table('vcd_ebook')->order('position DESC')->limit(1)->select('position')->fetchField() + 1;
                        $row = $this->db()->table('vcd_ebook')->insert($data);
                        $id = $row->getPrimary();
                    } else {
                        $this->db()->table('vcd_ebook')->wherePrimary($id)->update($data);
                    }

                    $dir = $this->container->get('ebooks') . '/' . $id;
                    $update = [];
                    FileSystem::createDir($dir);
                    if($img !== NULL && $img->isOk()) {
                        $imgFile = $dir . '/' . $img->getSanitizedName();
                        $img->move($imgFile);
                        $update['image'] = str_replace($this->container->get('www'), '', $imgFile);
                    }
                    if($ebook !== NULL && $ebook->isOk()) {
                        $ebookFile = $dir . '/' . $ebook->getSanitizedName();
                        $ebook->move($ebookFile);
                        $update['ebook'] = str_replace($this->container->get('www'), '', $ebookFile);
                    }
                    if(!empty($update)) {
                        $this->db()->table('vcd_ebook')->wherePrimary($id)->update($update);
                    }
                    $this->presenter->flashMessage('Uloženo.', 'success');
                    $this->presenter->redirect('ebooks');
                } else if($id !== NULL && $f->isSubmitted() === $f['delete']) {
                    $this->db()->table('vcd_ebook')->wherePrimary($id)->delete();
                    $this->presenter->flashMessage('Smazáno.', 'success');
                    $this->presenter->redirect('ebooks');
                }
            };
            if($id !== NULL) {
                $f->setValues($this->db()->table('vcd_ebook')->wherePrimary($id)->fetch());
            } else {
                $f['downloaded']->setValue(0);
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
