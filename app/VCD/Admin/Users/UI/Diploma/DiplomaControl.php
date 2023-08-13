<?php

namespace VCD\Admin\Users\UI;

use Hafo\NetteBridge\UI\DropzoneControl;
use Psr\Container\ContainerInterface;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Http\FileUpload;
use Nette\Utils\FileSystem;
use Nette\Utils\Finder;
use Nette\Utils\Html;

class DiplomaControl extends Control {

    private $container;

    function __construct(ContainerInterface $container, $id) {
        $this->container = $container;

        $this->onAnchor[] = function() use ($id) {
            $row = $this->db()->table('vcd_application_child')->wherePrimary($id)->fetch();
            if(!$row) {
                throw new BadRequestException;
            }
            $this->template->applicationChild = $row;

            $dir = $this->container->get('diplomas') . '/' . $id;
            $d = new DropzoneControl($this->container->get('www'), file_exists($dir) ? Finder::findFiles('*')->in($dir) : []);
            $d->setThumbnailFactory($this->dropzoneTemplate($dir, $d));
            $d->onUpload[] = function(FileUpload $file, DropzoneControl $control) use ($dir, $id) {
                if(!$file->isImage()) {
                    throw new BadRequestException;
                }
                FileSystem::createDir($dir);
                foreach(Finder::findFiles('*')->in($dir) as $existing => $foo) {
                    FileSystem::delete($existing);
                }
                $filename = $file->getSanitizedName();
                $img = $file->toImage();
                $img->resize(NULL, 100);
                $img->save($dir . '/thumb_' . $filename);
                $this->db()->table('vcd_application_child')->wherePrimary($id)->update([
                    'diploma' => str_replace($this->container->get('www'), '', $dir . '/' . $filename),
                    'diploma_thumb' => str_replace($this->container->get('www'), '', $dir . '/thumb_' . $filename),
                ]);
                $file->move($dir . '/' . $filename);
            };
            $d->onDelete[] = function($filename, DropzoneControl $control) use ($dir, $id) {
                FileSystem::delete($dir . '/' . $filename);
                $this->db()->table('vcd_application_child')->wherePrimary($id)->update([
                    'diploma' => NULL
                ]);
                $this->presenter->flashMessage('Diplom byl smazán.', 'success');
                $this->presenter->redirect('this');
            };
            $this->addComponent($d, 'dropzone');
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
