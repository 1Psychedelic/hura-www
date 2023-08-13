<?php

namespace VCD\Admin\Events\UI;

use Hafo\NetteBridge\UI\DropzoneControl;
use Nette\Database\SqlLiteral;
use Nette\Utils\Html;
use Psr\Container\ContainerInterface;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Database\Context;
use Nette\Http\FileUpload;
use Nette\Utils\FileSystem;
use Tomaj\Form\Renderer\BootstrapRenderer;

class EventDesignControl extends Control {

    private $container;

    function __construct(ContainerInterface $container, $event) {
        $this->container = $container;

        $this->onAnchor[] = function() use ($event) {
            $currentEvent = $this->db()->table('vcd_event')->wherePrimary($event)->fetch();
            if(!$currentEvent) {
                throw new ForbiddenRequestException;
            }
            $this->template->currentEvent = $currentEvent;

            $dir = $this->container->get('events') . '/' . $event;

            $f = new Form;
            $f->setRenderer(new BootstrapRenderer);
            $f->addUpload('img', 'Malý banner')->setRequired();//->addRule(Form::IMAGE);
            $f->addSubmit('save', 'Nahrát');
            $f->addProtection();
            $f->onSuccess[] = function(Form $f) use ($dir, $event) {
                if($f->isSubmitted() === $f['save']) {
                    $file = $f->getValues(TRUE)['img'];
                    /** @var FileUpload $file */
                    FileSystem::createDir($dir);
                    if($file->isOk()) {
                        $ext = explode('/', $file->getContentType())[1];
                        $name = $dir . '/banner_small.' . $ext;
                        $file->move($name);
                        $this->db()->table('vcd_event')->wherePrimary($event)->update([
                            'banner_small' => str_replace($this->container->get('www'), '', $name),
                            'updated_at' => new SqlLiteral('NOW()'),
                        ]);
                        $this->presenter->flashMessage('Obrázek byl nahrán.', 'success');
                    }
                    $this->presenter->redirect('this');
                }
            };
            $this->addComponent($f, 'bannerSmall');

            $f = new Form;
            $f->setRenderer(new BootstrapRenderer);
            $f->addUpload('img', 'Velký banner')->setRequired();//->addRule(Form::IMAGE);
            $f->addSubmit('save', 'Nahrát');
            $f->addProtection();
            $f->onSuccess[] = function(Form $f) use ($dir, $event) {
                $file = $f->getValues(TRUE)['img'];
                /** @var FileUpload $file */
                FileSystem::createDir($dir);
                if($file->isOk()) {
                    $ext = explode('/', $file->getContentType())[1];
                    $name = $dir . '/banner_large.' . $ext;
                    $file->move($name);
                    $this->db()->table('vcd_event')->wherePrimary($event)->update([
                        'banner_large' => str_replace($this->container->get('www'), '', $name),
                        'updated_at' => new SqlLiteral('NOW()'),
                    ]);
                    $this->presenter->flashMessage('Obrázek byl nahrán.', 'success');
                }
                $this->presenter->redirect('this');
            };
            $this->addComponent($f, 'bannerLarge');

            $gallery = $this->db()->table('vcd_event_image')->where('event', $event)->order('position ASC');
            $files = [];
            foreach($gallery as $photo) {
                $f = $dir . '/images/' . $photo['name'];
                if(file_exists($f)) {
                    $files[$f] = new \SplFileInfo($f);
                }
            }

            $dz = new DropzoneControl($dir . '/images', $files);
            $dz->setThumbnailFactory(function($name) use ($dir, $dz, $event) {
                $html = Html::el()->addHtml(
                    Html::el('a')->href($this->template->baseUri . str_replace($this->container->get('www'), '', $dir . '/images/' . $name))->target('_blank')->setText($name)
                )->addHtml(
                    Html::el('br')
                )->addHtml(
                    Html::el('a')->href($this->link('photoLeft!', ['event' => $event, 'name' => $name]))->setText('Doleva')
                )->addHtml(
                    ' '
                )->addHtml(
                    Html::el('a')->href($this->link('photoRight!', ['event' => $event, 'name' => $name]))->setText('Doprava')
                );
                $html->addHtml(
                    Html::el('br')
                )->addHtml(
                    Html::el('a')->href($dz->link('delete!', ['file' => $name]))->setText('Smazat')
                );
                return $html;
            });
            $dz->onUpload[] = function (FileUpload $file, DropzoneControl $control) use ($dir, $event) {
                if (!$file->isImage()) {
                    throw new ForbiddenRequestException();
                }

                FileSystem::createDir($dir . '/images');
                $name = $file->getSanitizedName();
                $ext = explode('/', $file->getContentType())[1];

                $img = $file->toImage();
                $thumb = clone $img;
                if ($img->getWidth() > $img->getHeight()) {
                    $thumb->resize(400, null);
                } else {
                    $thumb->resize(null, 400);
                }

                $this->db()->table('vcd_event_image')->where('event', $event)->where('name', $name)->delete();
                $this->db()->table('vcd_event_image')->insert([
                    'event' => $event,
                    'name' => $name,
                    'position' => (int)$this->db()->table('vcd_event_image')->where('event', $event)->select('MAX(position)')->fetchField() + 1,
                    'thumb_w' => $thumb->getWidth(),
                    'thumb_h' => $thumb->getHeight(),
                ]);

                $img->save($dir . '/images/' . $name);
                $thumb->save($dir . '/images/thumb_' . $name);

                $this->db()->table('vcd_event')->wherePrimary($event)->update(['updated_at' => new SqlLiteral('NOW()')]);
            };
            $dz->onDelete[] = function($photo, DropzoneControl $control) use ($dir, $event) {
                $currentPhoto = $this->db()->table('vcd_event_image')->where('event', $event)->where('name', $photo)->fetch();
                if(!$currentPhoto) {
                    throw new ForbiddenRequestException;
                }
                $file = $dir . '/images/' . $currentPhoto['name'];
                if(file_exists($file)) {
                    FileSystem::delete($file);
                }
                $thumb = $dir . '/images/thumb_' . $currentPhoto['name'];
                if(file_exists($thumb)) {
                    FileSystem::delete($thumb);
                }
                $this->db()->table('vcd_event_image')->where('event', $event)->where('name', $photo)->delete();
                $this->db()->table('vcd_event')->wherePrimary($event)->update(['updated_at' => new SqlLiteral('NOW()')]);
                $this->presenter->flashMessage('Fotka byla smazána.', 'success');
                $this->presenter->redirect('this');
            };

            $this->addComponent($dz, 'dropzone');

            $this->template->currentEvent = $this->db()->table('vcd_event')->wherePrimary($event)->fetch();
        };
    }

    function handlePhotoLeft($name, $event = NULL) {
        $row = $this->db()->table('vcd_event_image')->where('event', $event)->where('name', $name)->fetch();
        if(!$row) {
            throw new ForbiddenRequestException;
        }
        $prev = $this->db()->table('vcd_event_image')->where('event', $event)->where('position < ?', $row['position'])->order('position DESC')->fetch();
        if($prev) {
            $this->db()->table('vcd_event_image')->where('event', $event)->where('name', $prev['name'])->update(['position' => $row['position']]);
            $this->db()->table('vcd_event_image')->where('event', $event)->where('name', $name)->update(['position' => $prev['position']]);
        }
        $this->db()->table('vcd_event')->wherePrimary($event)->update(['updated_at' => new SqlLiteral('NOW()')]);
        $this->presenter->flashMessage('Fotka byla posunutá.', 'success');
        $this->presenter->redirect('this');
    }

    function handlePhotoRight($name, $event = NULL) {
        $row = $this->db()->table('vcd_event_image')->where('event', $event)->where('name', $name)->fetch();
        if(!$row) {
            throw new ForbiddenRequestException;
        }
        $next = $this->db()->table('vcd_event_image')->where('event', $event)->where('position > ?', $row['position'])->order('position ASC')->fetch();
        if($next) {
            $this->db()->table('vcd_event_image')->where('event', $event)->where('name', $next['name'])->update(['position' => $row['position']]);
            $this->db()->table('vcd_event_image')->where('event', $event)->where('name', $name)->update(['position' => $next['position']]);
        }
        $this->db()->table('vcd_event')->wherePrimary($event)->update(['updated_at' => new SqlLiteral('NOW()')]);
        $this->presenter->flashMessage('Fotka byla posunutá.', 'success');
        $this->presenter->redirect('this');
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->render();
    }

    private function db() {
        return $this->container->get(Context::class);
    }

}
