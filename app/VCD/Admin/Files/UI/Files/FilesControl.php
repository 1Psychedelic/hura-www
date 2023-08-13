<?php

namespace VCD\Admin\Files\UI;

use Hafo\NetteBridge\UI\DropzoneControl;
use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Http\FileUpload;
use Nette\IOException;
use Nette\Utils\FileSystem;
use Nette\Utils\Finder;
use Nette\Utils\Html;
use Tomaj\Form\Renderer\BootstrapRenderer;

class FilesControl extends Control {

    private $container;

    function __construct(ContainerInterface $container, $base, $dir = NULL) {
        $this->container = $container;
        
        $this->onAnchor[] = function() use ($base, $dir) {
            $this->template->dir = $dir;
            $this->template->dirParts = array_filter(explode('/', $dir));
            $this->template->dirs = Finder::findDirectories('*')->in($base . '/' . $dir);

            $f = new Form;
            $f->setRenderer(new BootstrapRenderer);
            $f->addText('name', 'Název')->setRequired();
            $f->addSubmit('create', 'Vytvořit složku');
            $f->addProtection();
            $f->onSuccess[] = function(Form $f) use ($base, $dir) {
                if($f->isSubmitted() === $f['create']) {
                    $name = $f->getValues(TRUE)['name'];
                    try {
                        FileSystem::createDir($base . '/' . $dir . '/' . $name);
                        $this->presenter->flashMessage('Složka byla vytvořena.', 'success');
                    } catch (IOException $e) {
                        $this->presenter->flashMessage('Složku se nepodařilo vytvořit.', 'danger');
                    }
                    $this->presenter->redirect('this');
                }
            };
            $this->addComponent($f, 'form');

            $this->addComponent($this->dropzone($base . '/' . $dir), 'dropzone');
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

}
