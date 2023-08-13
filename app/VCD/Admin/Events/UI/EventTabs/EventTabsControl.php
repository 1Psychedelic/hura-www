<?php

namespace VCD\Admin\Events\UI;

use Hafo\NetteBridge\UI\CKEditorInlineControl;
use Hafo\NetteBridge\UI\DropzoneControl;
use Psr\Container\ContainerInterface;
use Nette\Application\ForbiddenRequestException;
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

class EventTabsControl extends Control {

    private $container;

    function __construct(ContainerInterface $container, $event, $tab = NULL) {
        $this->container = $container;

        $this->onAnchor[] = function() use ($event, $tab) {
            $currentEvent = $this->db()->table('vcd_event')->wherePrimary($event)->fetch();
            if(!$currentEvent) {
                throw new ForbiddenRequestException;
            }
            $tabs = $this->db()->table('vcd_event_tab')->where('event', $event)->order('position ASC');
            $this->template->tabs = $tabs;
            $this->template->currentEvent = $currentEvent;
            $this->template->currentTab = $currentTab = $tab === NULL ? NULL : $this->db()->table('vcd_event_tab')->where('event = ? AND id = ?', [$event, $tab])->fetch();

            $f = new Form;
            $f->setRenderer(new BootstrapRenderer);
            $f->addText('name', 'Název')->setRequired();
            $f->addCKEditor('content', 'Obsah');
            $f->addProtection();
            $f->addSubmit('save', 'Uložit');
            if($tab !== NULL) {
                $f->addSubmit('delete', 'Smazat')->getControlPrototype()
                    ->attrs['onclick'] = 'if(!confirm("Opravdu smazat?")){return false;}';
                $f->addSubmit('left', 'Doleva');
                $f->addSubmit('right', 'Doprava');
            }
            $f->onSuccess[] = function(Form $f) use ($event, $tab, $currentTab) {
                if($f->isSubmitted() === $f['save']) {
                    $data = $f->getValues(TRUE);
                    $id = NULL;
                    $error = 0;
                    do {
                        try {
                            $data['slug'] = Strings::webalize($data['name']) . ($error !== 0 ? '-' . $error : '');
                            if ($tab !== NULL) {
                                $this->db()->table('vcd_event_tab')->wherePrimary($tab)->update($data);
                                $this->presenter->flashMessage('Uloženo.', 'success');
                            } else {
                                $data['event'] = $event;
                                $data['position'] = (int)$this->db()->table('vcd_event_tab')->where('event', $event)->select('MAX(position)')->fetchField() + 1;
                                $row = $this->db()->table('vcd_event_tab')->insert($data);
                                $this->presenter->flashMessage('Uloženo.', 'success');
                                $id = $row['id'];
                            }
                            $error = 0;
                        } catch(UniqueConstraintViolationException $e) {
                            $error++;
                        }
                    } while ($error !== 0);
                    $this->presenter->redirect('this', ['tab' => $tab === NULL ? $id : $tab]);
                } else if($f->isSubmitted() === $f['delete'] && $tab !== NULL) {
                    $this->db()->table('vcd_event_tab')->wherePrimary($tab)->delete();
                    $this->presenter->flashMessage('Záložka byla smazána.', 'success');
                    $this->presenter->redirect('this', ['tab' => NULL]);
                } else if(($f->isSubmitted() === $f['left'] || $f->isSubmitted() === $f['right'] ) && $tab !== NULL) {
                    $sign = $f->isSubmitted() === $f['left'] ? '<' : '>';
                    $prev = $this->db()->table('vcd_event_tab')->where('event = ? AND position ' . $sign . ' ?', [$event, $currentTab['position']])
                        ->order('position ' . ($sign === '<' ? 'DESC' : 'ASC'))->fetch();
                    if($prev) {
                        $this->db()->table('vcd_event_tab')->wherePrimary($prev['id'])->update(['position' => $currentTab['position']]);
                        $this->db()->table('vcd_event_tab')->wherePrimary($tab)->update(['position' => $prev['position']]);
                    }
                    $this->presenter->flashMessage('Záložka byla posunutá.', 'success');
                    $this->presenter->redirect('this');
                }
            };
            if($currentTab !== NULL) {
                $f->setValues($currentTab);
            }
            $this->addComponent($f, 'form');

            if($tab === NULL)
                return;

            $this->addComponent($this->dropzone($this->container->get('events') . '/' . $event), 'dropzone');
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
