<?php

namespace VCD\Admin\Emails\UI;

use Hafo\NetteBridge\UI\DropzoneControl;
use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Database\Context;
use Nette\Http\FileUpload;
use Nette\Utils\FileSystem;
use Nette\Utils\Finder;
use Nette\Utils\Html;
use Tomaj\Form\Renderer\BootstrapRenderer;

class EmailControl extends Control {

    private $container;

    function __construct(ContainerInterface $container, $id = NULL) {
        $this->container = $container;

        $this->onAnchor[] = function() use ($id) {
            $f = new Form;
            $f->setRenderer(new BootstrapRenderer);
            $f->addText('name', 'Předmět')->setRequired();
            $f->addTextArea('message', 'Zpráva', NULL, 8)->setRequired();
            $f->addProtection();
            $f->addSubmit('save', 'Uložit');
            if($id !== NULL) {
                $f->addSubmit('delete', 'Odstranit')->getControlPrototype()
                    ->attrs['onclick'] = 'if(!confirm("Opravdu smazat?")){return false;}';
            }
            $f->onSuccess[] = function(Form $f) use ($id) {
                if($f->isSubmitted() === $f['save']) {
                    $data = $f->getValues(TRUE);
                    if($id === NULL) {
                        $this->db()->table('vcd_email')->insert($data);
                        $this->presenter->flashMessage('Uloženo.', 'success');
                    } else {
                        $this->db()->table('vcd_email')->wherePrimary($id)->update($data);
                        $this->presenter->flashMessage('Uloženo.', 'success');
                    }
                    $this->presenter->redirect('emails');
                } else if($id !== NULL && $f->isSubmitted() === $f['delete']) {
                    $this->db()->table('vcd_email_attachment')->where('email', $id)->delete();
                    $this->db()->table('vcd_email')->wherePrimary($id)->delete();
                    $this->presenter->flashMessage('E-mail byl odstraněn.', 'success');
                    $this->presenter->redirect('emails');
                }
            };
            if($id !== NULL) {
                $row = $this->db()->table('vcd_email')->wherePrimary($id)->fetch();
                $f->setValues($row);
                $this->template->row = $row;
                $dir = $this->container->get('emails') . '/' . $id;
                $d = new DropzoneControl($this->container->get('www'), file_exists($dir) ? Finder::findFiles('*')->in($dir) : []);
                $d->setThumbnailFactory($this->dropzoneTemplate($dir, $d));
                $d->onUpload[] = function(FileUpload $file, DropzoneControl $control) use ($dir, $id) {
                    FileSystem::createDir($dir);
                    $filename = $file->getSanitizedName();
                    $this->db()->table('vcd_email_attachment')->insert([
                        'email' => $id,
                        'file' => str_replace($this->container->get('www'), '', $dir . '/' . $filename)
                    ]);
                    $file->move($dir . '/' . $filename);
                };
                $d->onDelete[] = function($filename, DropzoneControl $control) use ($dir, $id) {
                    FileSystem::delete($dir . '/' . $filename);
                    $this->db()->table('vcd_email_attachment')->where('email = ? AND file = ?', [$id, str_replace($this->container->get('www'), '', $dir . '/' . $filename)])->delete();
                    $this->presenter->flashMessage('Příloha byla smazána.', 'success');
                    $this->presenter->redirect('this');
                };
                $this->addComponent($d, 'dropzone');
            } else {
                $this->template->row = NULL;
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

}
