<?php

namespace VCD\UI\FrontModule\GalleryModule;

use Psr\Container\ContainerInterface;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Control;
use Nette\Database\Context;
use VCD\UI\FrontModule\EventsModule\EventTermControl;

class GalleryControl extends Control
{

    private $database;

    private $event;

    private $photo;

    private $events;

    function __construct(Context $database, $event = NULL, $photo = NULL)
    {
        $this->database = $database;
        $this->photo = $photo;

        if ($event !== NULL) {
            $row = $this->database->table('vcd_event')->where('slug = ? AND visible = 1', $event)->fetch();
            if (!$row) {
                throw new BadRequestException;
            }
            $this->event = $row;
        } else {
            $this->events = $events = $this->database->query('SELECT e.*, COUNT(p.id) AS photos FROM vcd_photo p LEFT JOIN vcd_event e ON e.id = p.event WHERE p.type = 0 AND p.visible = 1 GROUP BY e.id HAVING COUNT(p.id) > 0 ORDER BY e.starts DESC')->fetchAll();
            if (count($events) === 1) {
                foreach ($events as $event) {
                    $this->onAnchor[] = function () use ($event) {
                        $this->presenter->redirect('this', ['id' => $event['slug'], 'photo' => NULL]);
                    };
                }
            }
            foreach ($events as $event) {
                $this->addComponent(new EventTermControl($event['starts'], $event['ends'], FALSE), 't' . $event['id']);
            }
        }
    }


    function render()
    {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->event = NULL;
        if ($this->event !== NULL) {
            $this->template->event = $this->event;
            $this->template->currentPhoto = $currentPhoto = $this->photo === NULL ? NULL : $this->database->table('vcd_photo')->where('event = ? AND name = ? AND type = 0 AND visible = 1', [$this->event['id'], $this->photo])->fetch();

            $this->template->gallery = $this->database->table('vcd_photo')->where('event = ? AND type = 0 AND visible = 1', $this->event['id'])->order('position ASC');
            $this->template->thumbnail = function ($w, $h, $desiredHeight = 180) {

                if ($h > $desiredHeight) { // fit height
                    $scale = $desiredHeight / $h;
                } else {
                    $scale = 1;
                }
                $thumbW = (int)round($w * $scale);
                $thumbH = (int)round($h * $scale);

                return 'width:' . floor($thumbW) . 'px;height:' . floor($thumbH) . 'px';
            };

            if ($currentPhoto) {
                $this->template->previous = $this->database->table('vcd_photo')->where('event = ? AND type = 0 AND visible = 1 AND position < ?', [$this->event['id'], $currentPhoto['position']])->order('position DESC')->fetch();
                $this->template->next = $this->database->table('vcd_photo')->where('event = ? AND type = 0 AND visible = 1 AND position > ?', [$this->event['id'], $currentPhoto['position']])->order('position ASC')->fetch();;
            }
        } else {
            $this->template->events = $this->events;
        }
        $this->template->render();
    }

}

class GalleryControlFactory {

    private $container;

    function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    function create($event = NULL, $photo = NULL) {
        return new GalleryControl(
            $this->container->get(Context::class),
            $event,
            $photo
        );
    }

}

