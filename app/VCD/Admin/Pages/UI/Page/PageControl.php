<?php

namespace VCD\Admin\Pages\UI;

use Hafo\NetteBridge\UI\CKEditorInlineControl;
use Hafo\NetteBridge\UI\DropzoneControl;
use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Database\Context;
use Nette\Database\UniqueConstraintViolationException;
use Nette\Http\FileUpload;
use Nette\Http\IRequest;
use Nette\Utils\FileSystem;
use Nette\Utils\Finder;
use Nette\Utils\Html;
use Nette\Utils\Strings;
use Tomaj\Form\Renderer\BootstrapRenderer;

class PageControl extends Control {

    private $container;

    function __construct(ContainerInterface $container, $id = NULL, $html = FALSE) {
        $this->container = $container;

        $this->onAnchor[] = function() use ($id, $html) {
            $this->template->currentPage = $currentPage = $id === NULL ? NULL : $this->db()->table('vcd_page')->wherePrimary($id)->fetch();
            $this->template->html = $html;
            $f = new Form;
            $f->setRenderer(new BootstrapRenderer);
            if($id === NULL || $currentPage['special'] === 0) {
                $f->addText('name', 'Název')->setRequired();
                $f->addTextArea('keywords', 'Klíčová slova oddělená čárkou');
            }
            if($html) {
                $f->addTextArea('content', 'Obsah');
            } else {
                $f->addCKEditor('content', 'Obsah');
            }
            $f->addSubmit('save', 'Uložit');
            if($id !== NULL && $currentPage['special'] === 0) {
                $f->addSubmit('delete', 'Smazat')->getControlPrototype()
                    ->attrs['onclick'] = 'if(!confirm("Opravdu smazat?")){return false;}';
            }
            $f->addProtection();
            $f->onSuccess[] = function(Form $f) use ($id, $currentPage) {
                if($f->isSubmitted() === $f['save']) {
                    $data = $f->getValues(TRUE);
                    $error = 0;
                    do {
                        try {
                            if($id === NULL || $currentPage['special'] === 0) {
                                $data['slug'] = Strings::webalize($data['name']) . ($error !== 0 ? '-' . $error : '');
                            }
                            if ($id !== NULL) {
                                $this->db()->table('vcd_page')->where(['slug' => $id])->update($data);
                                $this->presenter->flashMessage('Uloženo.', 'success');
                            } else {
                                $this->db()->table('vcd_page')->insert($data);
                                $id = $data['slug'];
                                $this->presenter->flashMessage('Uloženo.', 'success');
                            }
                            $error = 0;
                        } catch (UniqueConstraintViolationException $e) {
                            $error++;
                        }
                    } while ($error !== 0);
                    $this->presenter->redirect('this', ['id' => $id]);
                } else if($id !== NULL && $currentPage['special'] === 0 && $f->isSubmitted() === $f['delete']) {
                    $this->db()->table('vcd_page')->where(['slug' => $id])->delete();
                    $this->presenter->flashMessage('Stránka byla smazána.', 'success');
                    $this->presenter->redirect('pages');
                }
            };
            if($id !== NULL) {
                $f->setValues($currentPage);
            }
            $this->addComponent($f, 'form');
            if($id === NULL)
                return;
            $this->template->id = $id;
            $this->addComponent($this->dropzone($this->container->get('page') . '/' . $id), 'dropzone');
        };
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->render();
    }

    private function dropzone($dir) {
        $d = new DropzoneControl($this->container->get('www'), file_exists($dir) ? Finder::findFiles('*')->in($dir) : []);
        $d->setThumbnailFactory($this->dropzoneTemplate($dir, $d));
        $d->onUpload[] = function(FileUpload $file, DropzoneControl $control) use ($dir) {
            FileSystem::createDir($dir);
            $filename = $file->getSanitizedName();
            $file->move($dir . '/' . $filename);
        };
        $d->onDelete[] = function($filename, DropzoneControl $control) use ($dir) {
            FileSystem::delete($dir . '/' . $filename);
            $this->presenter->redirect('this');
        };
        return $d;
    }

    private function dropzoneTemplate($dir, DropzoneControl $control) {
        return function($name) use ($dir, $control) {
            return Html::el()->addHtml(
                Html::el('a')->href($this->template->baseUri . str_replace($this->container->get('www'), '', $dir . '/' . $name))->target('blank')->setText($name)
            )->addHtml(
                Html::el('br')
            )->addHtml(
                Html::el('a')->href($control->link('delete!', ['file' => $name]))->setText('Smazat')
            );
        };
    }

    private function db() {
        return $this->container->get(Context::class);
    }

}
