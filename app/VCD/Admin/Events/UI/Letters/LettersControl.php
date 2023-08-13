<?php

namespace VCD\Admin\Events\UI;

use Hafo\DI\Container;
use Hafo\NetteBridge\UI\DropzoneControl;
use Hafo\Persona\HumanAge;
use Psr\Container\ContainerInterface;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Http\FileUpload;
use Nette\Utils\FileSystem;
use Nette\Utils\Finder;
use Nette\Utils\Html;
use Nette\Utils\Image;
use Nette\Utils\Random;
use Nextras\Dbal\Connection;
use Tomaj\Form\Renderer\BootstrapRenderer;
use VCD2\Events\Event;
use VCD2\Orm;
use VCD2\PostOffice\Letter;
use VCD2\PostOffice\Service\PostOfficeAdmin;
use VCD2\Users\User;

class LettersControl extends Control {
    
    private $container;

    private $event;
    
    function __construct(Container $container, $event) {
        $this->container = $container;
        
        $this->onAnchor[] = function() use ($event) {

            /** @var Event $eventEntity */
            $eventEntity = $this->container->get(Orm::class)->events->get($event);
            if($eventEntity === NULL) {
                throw new BadRequestException;
            }

            $this->template->event = $this->event = $eventEntity;

            $this->template->users = $users = $eventEntity->acceptedUsers;

            $this->template->age = $age = function($dateBorn) {
                return (new HumanAge($dateBorn))->yearsAt(new \DateTime);
            };

            $userOptions = [];
            foreach($users as $user) {
                $userOption = sprintf('#%s %s [', $user->id, $user->name);
                foreach($user->findAcceptedApplicationsForEvent($eventEntity) as $application) {
                    foreach($application->children as $child) {
                        $userOption .= sprintf('%s %s, ', $child->name, $age($child->dateBorn));
                    }
                }
                $userOptions[$user->id] = substr($userOption, 0, -2) . ']';
            }
            $this->template->userOptions = $userOptions;

            $this->template->children = function($userOption) {
                preg_match('#\[(.*?)\]#', $userOption, $match);
                return $match[1];
            };

            $temp = $this->container->get('letters') . '/temp/' . $event;
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
                $this->presenter->flashMessage('Dopis byl smazán.', 'success');
                $this->presenter->redirect('this');
            };
            $this->addComponent($d, 'dropzone');

            if(count($tempFiles) > 0) {
                $f = new Form;
                $f->setRenderer(new BootstrapRenderer);
                $i = 0;
                foreach($tempFiles as $path => $fileinfo) {
                    /** @var \SplFileInfo $fileinfo */
                    $filename = $fileinfo->getFilename();
                    $a = Html::el('a')->addAttributes(['href' => $this->template->baseUrl . '/www' . str_replace($this->container->get('www'), '', $temp . '/' . $filename), 'target' => '_blank']);
                    $img = Html::el('img')->addAttributes(['style' => 'max-height:100px', 'class' => 'img-responsive'])->src($this->template->baseUrl . '/www' . str_replace($this->container->get('www'), '', $temp . '/' . $filename));
                    $label = Html::el()->addHtml($a->setHtml($img))->addHtml('<br>' . $filename);
                    $f->addXSelect($i, $label, $userOptions)
                        ->setPrompt('(není vybráno)');
                    $i++;
                }
                $f->addProtection();
                $f->addSubmit('save', 'Přiřadit uživatelům');
                $f->onSuccess[] = function(Form $f) use ($tempFiles, $event, $users, $eventEntity) {
                    if($f->isSubmitted() === $f['save']) {
                        $data = $f->getValues(TRUE);
                        $i = 0;
                        foreach($tempFiles as $path => $fileinfo) {
                            /** @var \SplFileInfo $fileinfo */
                            $filename = Random::generate(4) . '_' . $fileinfo->getFilename();
                            $user = $data[$i];
                            if($user !== NULL) {
                                $dir = $this->container->get('letters') . '/' . $user . '/' . $event;
                                FileSystem::createDir($dir);
                                FileSystem::rename($path, $dir . '/' . $filename);
                                //$img = Image::fromFile($dir . '/' . $filename);
                                $letter = new Letter($users[$user], $eventEntity, Letter::DIRECTION_CHILD_TO_PARENT, NULL, str_replace($this->container->get('www'), '', $dir . '/' . $filename));
                                $this->container->get(Orm::class)->persist($letter);
                            }
                            $i++;
                        }
                        $this->container->get(Orm::class)->flush();
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

    function handleClearTemp() {
        $temp = $this->container->get('letters') . '/temp/' . $this->event->id;
        FileSystem::delete($temp);
        $this->presenter->flashMessage('Promazáno.', 'success');
        $this->presenter->redirect('this');
    }

    function handleRemove($id) {
        $orm = $this->container->get(Orm::class);
        $letter = $orm->letters->get($id);
        if(!$letter || $letter->imageUrl === NULL) {
            return;
        }
        $file = $this->container->get('www') . '/' . $letter->imageUrl;
        if(file_exists($file)) {
            $fileInfo = new \SplFileInfo($file);
            FileSystem::rename($file, $this->container->get('letters') . '/temp/' . $this->event->id . '/' . $fileInfo->getBasename());
        }
        $orm->remove($letter);
        $orm->flush();
        $this->presenter->flashMessage('Odebráno.', 'success');
        $this->redirect('this');
    }

    function handleDelete($id) {
        $orm = $this->container->get(Orm::class);
        $letter = $orm->letters->get($id);
        if(!$letter || $letter->imageUrl === NULL) {
            return;
        }
        $file = $this->container->get('www') . '/' . $letter->imageUrl;
        if(file_exists($file)) {
            FileSystem::delete($file);
        }
        $orm->remove($letter);
        $orm->flush();
        $this->presenter->flashMessage('Smazáno.', 'success');
        $this->redirect('this');
    }

    function handlePublish() {
        $this->container->get(PostOfficeAdmin::class)->publish($this->event);

        $this->presenter->flashMessage('Dopisy publikovány, e-maily rozeslány.', 'success');
        $this->redirect('this');
    }
    
    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->render();
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
