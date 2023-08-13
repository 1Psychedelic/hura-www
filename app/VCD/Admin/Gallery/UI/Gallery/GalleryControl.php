<?php

namespace VCD\Admin\Gallery\UI;

use Hafo\NetteBridge\UI\DropzoneControl;
use Psr\Container\ContainerInterface;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Http\FileUpload;
use Nette\Utils\FileSystem;
use Nette\Utils\Html;
use Nette\Utils\Image;
use Nette\Utils\UnknownImageFileException;

class GalleryControl extends Control {

    private $container;

    function __construct(ContainerInterface $container, $event = NULL, $type = 0) {
        $this->container = $container;

        $this->onAnchor[] = function() use ($event, $type) {
            $this->template->currentEvent = $currentEvent = $event === NULL ? NULL : $this->db()->table('vcd_event')->wherePrimary($event)->fetch();
            if(!$currentEvent && $type === 0) {
                throw new ForbiddenRequestException;
            }
            $this->template->type = $type;
            $this->template->gallery = $gallery = $this->db()->table('vcd_photo')->where('event', $event)->where('type', $type)->order('position ASC');
            $dir = $this->container->get($type === 0 ? 'photos' : 'lostfound') . '/' . (int)$event;
            $files = [];
            foreach($gallery as $photo) {
                $f = $dir . '/' . $photo['name'];
                if(file_exists($f)) {
                    $files[$f] = new \SplFileInfo($f);
                }
            }
            $d = new DropzoneControl($this->container->get('www'), $files);
            $d->setThumbnailFactory(function($name) use ($dir, $d, $event, $type) {
                $visible = $this->db()->table('vcd_photo')->where('event', $event)->where('type', $type)->where('name', $name)->select('visible')->fetchField();
                $html = Html::el()->addHtml(
                    Html::el('a')->href($this->template->baseUri . str_replace($this->container->get('www'), '', $dir . '/' . $name))->target('_blank')->setText($name)
                )->addHtml(
                    Html::el('br')
                )->addHtml(
                    Html::el('a')->href($this->link('photoLeft!', ['event' => $event, 'type' => $type, 'name' => $name]))->setText('Doleva')
                )->addHtml(
                    ' '
                )->addHtml(
                    Html::el('a')->href($this->link('photoRight!', ['event' => $event, 'type' => $type, 'name' => $name]))->setText('Doprava')
                )->addHtml(
                    Html::el('br')
                )->addHtml(
                    Html::el('a')->href($this->link('photoSetVisible!', [
                        'event' => $event,
                        'type' => $type,
                        'name' => $name,
                        'visible' => !$visible
                    ]))->setHtml(Html::el('span')->class('glyphicon glyphicon-' . ($visible ? 'globe' : 'lock')))
                );
                if($type === 0) {
                    $html->addHtml(
                        Html::el('br')
                    )->addHtml(Html::el('a')->href($this->link('photoSetMain!', [
                        'event' => $event,
                        'name' => $name
                    ]))->setText('Jako úvodní'));
                }
                $html->addHtml(
                    Html::el('br')
                )->addHtml(
                    Html::el('a')->href($d->link('delete!', ['file' => $name]))->setText('Smazat')
                );
                return $html;
            });
            $d->onUpload[] = function(FileUpload $file, DropzoneControl $control) use ($dir, $event, $type) {
                if($file->isImage()) {
                    FileSystem::createDir($dir);
                    $name = $file->getSanitizedName();
                    $ext = explode('/', $file->getContentType())[1];

                    $this->db()->table('vcd_photo')->where('event', $event)->where('type', $type)->where('name', $name)->delete();
                    $this->db()->table('vcd_photo')->insert([
                        'event' => $event,
                        'name' => $name,
                        'type' => $type,
                        'position' => (int)$this->db()->table('vcd_photo')->where('event', $event)->where('type', $type)->select('MAX(position)')->fetchField() + 1,
                        'visible' => 0
                    ]);

                    $img = $file->toImage();
                    if($img->getWidth() > $img->getHeight()) {
                        $img->resize(2000, NULL);
                    } else {
                        $img->resize(NULL, 2000);
                    }
                    $thumb = clone $img;
                    if($img->getWidth() > $img->getHeight()) {
                        $thumb->resize(250, NULL);
                    } else {
                        $thumb->resize(NULL, 250);
                    }

                    $this->db()->table('vcd_photo')->where('event', $event)->where('type', $type)->where('name', $name)->delete();
                    $this->db()->table('vcd_photo')->insert([
                        'event' => $event,
                        'name' => $name,
                        'type' => $type,
                        'position' => (int)$this->db()->table('vcd_photo')->where('event', $event)->where('type', $type)->select('MAX(position)')->fetchField() + 1,
                        'visible' => 0,
                        'thumb_w' => $thumb->getWidth(),
                        'thumb_h' => $thumb->getHeight()
                    ]);

                    $img->save($dir . '/' . $name);
                    $thumb->save($dir . '/thumb_' . $name);
                } else {
                    throw new ForbiddenRequestException;
                }
            };
            $d->onDelete[] = function($photo, DropzoneControl $control) use ($event, $type) {
                $currentEvent = $event === NULL ? NULL : $this->db()->table('vcd_event')->wherePrimary($event)->fetch();
                if(!$currentEvent && $type === 0) {
                    throw new ForbiddenRequestException;
                }
                $dir = $this->container->get($type === 0 ? 'photos' : 'lostfound') . '/' . (int)$event;
                $currentPhoto = $this->db()->table('vcd_photo')->where('event', $event)->where('type', $type)->where('name', $photo)->fetch();
                if(!$currentPhoto) {
                    throw new ForbiddenRequestException;
                }
                $file = $dir . '/' . $currentPhoto['name'];
                if(file_exists($file)) {
                    FileSystem::delete($file);
                }
                $thumb = $dir . '/thumb_' . $currentPhoto['name'];
                if(file_exists($thumb)) {
                    FileSystem::delete($thumb);
                }
                $this->db()->table('vcd_photo')->where('event', $event)->where('type', $type)->where('name', $photo)->delete();
                $this->presenter->flashMessage('Fotka byla smazána.', 'success');
                $this->presenter->redirect('photos', ['event' => $event, 'type' => $type]);
            };
            $this->addComponent($d, 'dropzone');
        };
    }

    /** @internal maintenance */
    function handlePhotosSize() {
        $types = [
            0 => $this->container->get('photos'),
            1 => $this->container->get('lostfound')
        ];
        foreach($this->db()->table('vcd_photo')->where('thumb_w = 0 OR thumb_h = 0') as $row) {
            $file = $types[$row['type']] . '/' . $row['event'] . '/thumb_' . $row['name'];
            if(!file_exists($file)) {
                continue;
            }
            try {
                $img = Image::fromFile($file);
            } catch (UnknownImageFileException $e) {
                continue;
            }
            $this->db()->table('vcd_photo')->wherePrimary($row['id'])->update([
                'thumb_w' => $img->getWidth(),
                'thumb_h' => $img->getHeight()
            ]);
        }
        $this->presenter->redirect('this');
    }

    function handlePhotoSetMain($name, $event = NULL) {
        $row = $this->db()->table('vcd_photo')->where('event', $event)->where('type = 0')->where('name', $name)->fetch();
        if($row) {

            // make thumbnail
            $dir = $this->container->get('photos') . '/' . $event;
            $img = Image::fromFile($dir . '/' . $name);
            if($img->getWidth() > $img->getHeight()) {
                $img->resize(500, NULL);
            } else {
                $img->resize(NULL, 500);
            }
            $img->save($dir . '/list_' . $name);

            $this->db()->table('vcd_event')->wherePrimary($event)->update([
                'gallery_photo' => 'list_' . $row['name']
            ]);
        }
        $this->presenter->flashMessage('Fotka byla nastavena jako úvodní.', 'success');
        $this->presenter->redirect('photos', ['event' => $event, 'type' => 0]);
    }

    function handlePhotosSetVisible($visible, $event = NULL, $type = 0) {
        $this->db()->table('vcd_photo')->where('event', $event)->where('type', $type)->update([
            'visible' => (bool)$visible
        ]);
        $this->presenter->flashMessage('Viditelnost fotek byla nastavena.', 'success');
        $this->presenter->redirect('photos', ['event' => $event, 'type' => $type]);
    }

    function handlePhotosDelete($event = NULL, $type = 0) {
        $dir = $this->container->get($type === 0 ? 'photos' : 'lostfound') . '/' . $event;
        foreach($this->db()->table('vcd_photo')->where('event', $event)->where('type', $type) as $photo) {
            if(file_exists($dir . '/' . $photo['name'])) {
                FileSystem::delete($dir . '/' . $photo['name']);
            }
            if(file_exists($dir . '/thumb_' . $photo['name'])) {
                FileSystem::delete($dir . '/thumb_' . $photo['name']);
            }
        }
        $this->db()->table('vcd_photo')->where('event', $event)->where('type', $type)->delete();
        $this->presenter->flashMessage('Fotky byly smazány.', 'success');
        $this->presenter->redirect('photos', ['event' => $event, 'type' => $type]);
    }

    function handlePhotoSetVisible($visible, $name, $event = NULL, $type = 0) {
        $row = $this->db()->table('vcd_photo')->where('event', $event)->where('type', $type)->where('name', $name)->fetch();
        if(!$row) {
            throw new ForbiddenRequestException;
        }
        $this->db()->table('vcd_photo')->where('event', $event)->where('type', $type)->where('name', $name)->update([
            'visible' => (bool)$visible
        ]);
        $this->presenter->flashMessage('Viditelnost fotky byla nastavena.', 'success');
        $this->presenter->redirect('photos', ['event' => $event, 'type' => $type]);
    }

    function handlePhotoLeft($name, $event = NULL, $type = 0) {
        $row = $this->db()->table('vcd_photo')->where('event', $event)->where('type', $type)->where('name', $name)->fetch();
        if(!$row) {
            throw new ForbiddenRequestException;
        }
        $prev = $this->db()->table('vcd_photo')->where('event', $event)->where('type', $type)->where('position < ?', $row['position'])->order('position DESC')->fetch();
        if($prev) {
            $this->db()->table('vcd_photo')->where('event', $event)->where('type', $type)->where('name', $prev['name'])->update(['position' => $row['position']]);
            $this->db()->table('vcd_photo')->where('event', $event)->where('type', $type)->where('name', $name)->update(['position' => $prev['position']]);
        }
        $this->presenter->flashMessage('Fotka byla posunutá.', 'success');
        $this->presenter->redirect('photos', ['event' => $event, 'type' => $type]);
    }

    function handlePhotoRight($name, $event = NULL, $type = 0) {
        $row = $this->db()->table('vcd_photo')->where('event', $event)->where('type', $type)->where('name', $name)->fetch();
        if(!$row) {
            throw new ForbiddenRequestException;
        }
        $next = $this->db()->table('vcd_photo')->where('event', $event)->where('type', $type)->where('position > ?', $row['position'])->order('position ASC')->fetch();
        if($next) {
            $this->db()->table('vcd_photo')->where('event', $event)->where('type', $type)->where('name', $next['name'])->update(['position' => $row['position']]);
            $this->db()->table('vcd_photo')->where('event', $event)->where('type', $type)->where('name', $name)->update(['position' => $next['position']]);
        }
        $this->presenter->flashMessage('Fotka byla posunutá.', 'success');
        $this->presenter->redirect('photos', ['event' => $event, 'type' => $type]);
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->render();
    }

    private function db() {
        return $this->container->get(Context::class);
    }

}
