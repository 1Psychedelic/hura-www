<?php

namespace VCD\UI\FrontModule\GalleryModule;

use Hafo\NetteBridge\Forms\FormFactory;
use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Database\Context;
use Nette\Security\User;
use VCD\Notifications\Notifications;
use VCD\UI\FrontModule\EventsModule\EventTermControl;

class LostFoundControl extends Control
{

    private $database;

    private $user;

    private $selectedItem;

    private $selectedEvent;

    function __construct(Context $database, User $user, FormFactory $formFactory, Notifications $notifications, $selectedEvent = NULL, $selectedItem = NULL)
    {
        $this->database = $database;
        $this->user = $user;
        $this->selectedItem = $selectedItem;
        $this->selectedEvent = $selectedEvent;

        if ($selectedItem && $this->user->isLoggedIn()) {
            $row = $database->table('vcd_photo')->where('event', $selectedEvent)->where('name = ? AND type = 1 AND visible = 1 AND claimed_by IS NULL', $selectedItem)->fetch();
            if ($row) {
                $this->onAnchor[] = function () use ($database, $user, $formFactory, $notifications, $selectedEvent, $selectedItem) {
                    $f = $formFactory->create();
                    $f->addSubmit('claim', 'Patří věc na fotce vám? Klikněte zde!');
                    $f->onSuccess[] = function (Form $f) use ($database, $user, $notifications, $selectedEvent, $selectedItem) {
                        $database->table('vcd_photo')->where('event', $selectedEvent)->where('name = ? AND type = 1 AND visible = 1 AND claimed_by IS NULL', $selectedItem)
                            ->update([
                                'claimed_by' => $user->id
                            ]);
                        $notifications->add('Uživatel ' . $user->identity->data['name'] . ' se přihlásil o věc mezi ztráty a nálezy.', $user->id);
                        $this->presenter->flashMessage('Děkujeme za informaci, budeme vás kontaktovat.', 'success');
                        $this->presenter->redirect('this');
                    };
                    $this->addComponent($f, 'form');
                };
            }
        }
    }

    function render()
    {
        $this->template->setFile(__DIR__ . '/default.latte');
        $items = [];
        $events = $this->database->table('vcd_photo')->select('event')->where('type = 1 AND visible = 1')->group('event')->fetchPairs(NULL, 'event');
        foreach ($this->database->table('vcd_event')->where('id IN ?', $events)->order('ends DESC')->fetchPairs(NULL, 'id') as $event) {
            foreach ($this->database->table('vcd_photo')->where('type = 1 AND visible = 1 AND event = ?', $event)->order('position ASC') as $row) {
                $items[] = $row;
            }
        }
        foreach ($this->database->table('vcd_photo')->where('type = 1 AND visible = 1 AND event IS NULL')->order('position ASC') as $row) {
            $items[] = $row;
        }
        $this->template->items = $items;
        $this->template->selectedItem = NULL;
        if ($this->selectedItem) {
            $this->template->selectedItem = $selectedItem = $this->database->table('vcd_photo')->where('event', $this->selectedEvent)->where('type = 1 AND visible = 1 AND name = ?', $this->selectedItem)->fetch();
            $this->template->previous = NULL;
            $this->template->next = NULL;

            $previous = NULL;
            $nextIsNext = FALSE;
            foreach ($items as $item) {
                if ($selectedItem['id'] === $item['id']) {
                    $this->template->previous = $previous;
                    $nextIsNext = TRUE;
                } else if ($nextIsNext) {
                    $this->template->next = $item;
                    break;
                }
                $previous = $item;
            }
        }
        $this->template->event = function ($id) {
            $row = $this->database->table('vcd_event')->wherePrimary($id)->fetch();
            ob_start();
            $control = (new EventTermControl($row['starts'], $row['ends'], FALSE));
            $control->setParent($this, $id);
            $control->render();
            $term = ob_get_clean();
            return $row['name'] . ' (' . trim($term) . ')';
        };
        $this->template->thumbnail = function ($w, $h) {

            if ($h > 180) { // fit height
                $scale = 180 / $h;
            } else {
                $scale = 1;
            }
            $thumbW = (int)round($w * $scale);
            $thumbH = (int)round($h * $scale);

            return 'width:' . floor($thumbW) . 'px;height:' . floor($thumbH) . 'px';
        };
        $this->template->render();
    }

}

class LostFoundControlFactory {

    private $container;

    function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    function create($selectedEvent = NULL, $selectedItem = NULL) {
        return new LostFoundControl(
            $this->container->get(Context::class),
            $this->container->get(User::class),
            $this->container->get(FormFactory::class),
            $this->container->get(Notifications::class),
            $selectedEvent,
            $selectedItem
        );
    }

}
