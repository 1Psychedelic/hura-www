<?php

namespace VCD\Admin\Events\UI;

use Hafo\NetteBridge\UI\DropzoneControl;
use Hafo\Persona\HumanAge;
use Psr\Container\ContainerInterface;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Database\Context;
use Nette\Http\FileUpload;
use Nette\Utils\FileSystem;
use Nette\Utils\Finder;
use Nette\Utils\Html;
use Nette\Utils\Image;
use Tomaj\Form\Renderer\BootstrapRenderer;
use VCD2\Events\Event;
use VCD2\Orm;

class DiplomasControl extends Control {

    private $container;

    function __construct(ContainerInterface $container, $event) {
        $this->container = $container;

        $this->onAnchor[] = function() use ($event) {

            /** @var Event $entity */
            $entity = $this->container->get(Orm::class)->events->get($event);
            if($entity === NULL) {
                throw new BadRequestException;
            }

            $this->template->event = $entity;
            $this->template->children = $children = $entity->acceptedChildren;

            $temp = $this->container->get('diplomas') . '/temp/' . $event;
            $tempFiles = file_exists($temp) ? iterator_to_array(Finder::findFiles('*')->in($temp)) : [];
            ksort($tempFiles);
            $d = new DropzoneControl($this->container->get('www'), $tempFiles);
            $d->setThumbnailFactory($this->dropzoneTemplate($temp, $d));
            $d->onUpload[] = function(FileUpload $file, DropzoneControl $control) use ($temp) {
                if(!$file->isImage()) {
                    throw new BadRequestException;
                }
                FileSystem::createDir($temp);
                $filename = $file->getSanitizedName();
                $file->move($temp . '/' . $filename);
            };
            $d->onDelete[] = function($filename, DropzoneControl $control) use ($temp) {
                FileSystem::delete($temp . '/' . $filename);
                $this->presenter->flashMessage('Diplom byl smazán.', 'success');
                $this->presenter->redirect('this');
            };
            $this->addComponent($d, 'dropzone');

            $this->template->age = $age = function($dateBorn) {
                return (new HumanAge($dateBorn))->yearsAt(new \DateTime);
            };

            if(count($tempFiles) > 0) {
                $childrenOptions = [];
                foreach($children as $child) {
                    $childrenOptions[$child->id] = '#' . $child->id . ' ' . $child->name . ' (' . $age($child->dateBorn) . ' let)';
                }

                $f = new Form;
                $f->setRenderer(new BootstrapRenderer);
                $i = 0;
                foreach($tempFiles as $path => $fileinfo) {
                    /** @var \SplFileInfo $fileinfo */
                    $filename = $fileinfo->getFilename();
                    $f->addXSelect($i, Html::el('img')->addAttributes(['style' => 'max-height:100px', 'class' => 'img-responsive'])->src($this->template->baseUrl . '/www' . str_replace($this->container->get('www'), '', $temp . '/' . $filename)), $childrenOptions)
                        ->setPrompt('(není vybráno)');
                    $i++;
                }
                $f->addProtection();
                $f->addSubmit('save', 'Uložit');
                $f->onSuccess[] = function(Form $f) use ($tempFiles, $event) {
                    if($f->isSubmitted() === $f['save']) {
                        $data = $f->getValues(TRUE);
                        $i = 0;
                        foreach($tempFiles as $path => $fileinfo) {
                            /** @var \SplFileInfo $fileinfo */
                            $filename = $fileinfo->getFilename();
                            $child = $data[$i];
                            if($child !== NULL) {
                                $dir = $this->container->get('diplomas') . '/' . $child . '/' . $event;
                                FileSystem::createDir($dir);
                                FileSystem::rename($path, $dir . '/' . $filename);
                                $img = Image::fromFile($dir . '/' . $filename);
                                $img->resize(NULL, 100);
                                $img->save($dir . '/thumb_' . $filename);
                                $this->db()->table('vcd_application_child')->wherePrimary($child)->update([
                                    'diploma' => str_replace($this->container->get('www'), '', $dir . '/' . $filename),
                                    'diploma_thumb' => str_replace($this->container->get('www'), '', $dir . '/thumb_' . $filename),
                                ]);
                            }
                            $i++;
                        }
                        $this->redirect('this');
                    }
                };
                $this->addComponent($f, 'form');
                $this->template->showForm = TRUE;
            } else {
                $this->template->showForm = FALSE;
            }
        };
    }

    function handleDelete($id) {
        $row = $this->db()->table('vcd_application_child')->where('id = ? AND diploma IS NOT NULL AND diploma_thumb IS NOT NULL', $id)->fetch();
        if(!$row) {
            return;
        }
        $diploma = $this->container->get('www') . '/' . $row['diploma'];
        $thumb = $this->container->get('www') . '/' . $row['diploma_thumb'];
        if(file_exists($diploma)) {
            FileSystem::delete($diploma);
        }
        if(file_exists($thumb)) {
            FileSystem::delete($thumb);
        }
        $this->db()->table('vcd_application_child')->wherePrimary($id)->update([
            'diploma' => NULL,
            'diploma_thumb' => NULL,
        ]);
        $this->redirect('this');
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
                Html::el('a')->href($this->template->baseUri . str_replace($this->container->get('www'), '', $dir . '/' . $name))->target('_blank')->setText($name)
            )->addHtml(
                Html::el('br')
            )->addHtml(
                Html::el('a')->href($control->link('delete!', ['file' => $name]))->setText('Smazat')
            );
        };
    }

}
