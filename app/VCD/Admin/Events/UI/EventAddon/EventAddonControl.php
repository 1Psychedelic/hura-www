<?php

namespace VCD\Admin\Events\UI;

use Hafo\DI\Container;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nextras\Dbal\ForeignKeyConstraintViolationException;
use Tomaj\Form\Renderer\BootstrapRenderer;
use VCD2\Discounts\Discount;
use VCD2\Events\EventAddon;
use VCD2\Orm;

class EventAddonControl extends Control
{
    public function __construct(Container $container, $event, $addon = null)
    {
        $orm = $container->get(Orm::class);
        $this->onAnchor[] = function () use ($event, $addon, $orm) {
            $f = new Form;
            $f->getElementPrototype()->addAttributes(['autocomplete' => 'off']);
            $f->setRenderer(new BootstrapRenderer);
            $f->addText('name', 'Název')->setRequired();
            $f->addText('price', 'Cena')->setRequired();
            $f->addCheckbox('enabled', 'Aktivní');
            $f->addTextArea('description', 'Popis');
            $f->addText('icon', 'Obrázek')->setRequired();
            $f->addText('linkUrl', 'URL odkazu')->setNullable();
            $f->addText('linkText', 'Text odkazu')->setNullable();
            $f->addProtection();
            $f->addSubmit('save', 'Uložit');
            if ($addon !== null) {
                $f->addSubmit('delete', 'Odstranit');
            }
            $f->onSuccess[] = function (Form $f) use ($event, $addon, $orm) {
                if ($f->isSubmitted() === $f['save']) {
                    $data = $f->getValues(true);
                    $addonEntity = $addon === null
                        ? new EventAddon($orm->events->get($event))
                        : $orm->eventAddons->get($addon);
                    $addonEntity->setValues($data);
                    $orm->persistAndFlush($addonEntity);
                    $this->presenter->flashMessage('Doplněk uložen.', 'success');
                    $this->presenter->redirect('eventAddons', ['event' => $event]);
                } elseif ($addon !== null && $f->isSubmitted() === $f['delete']) {
                    try {
                        $orm->remove($orm->eventAddons->get($addon));
                    } catch (ForeignKeyConstraintViolationException $e) {
                        $this->presenter->flashMessage('Doplněk nelze smazat, protože již byl použit v nějaké přihlášce.', 'danger');
                        $this->presenter->redirect('this');
                    }
                    $orm->flush();
                    $this->presenter->flashMessage('Doplněk odstraněn.', 'success');
                    $this->presenter->redirect('eventAddons', ['event' => $event]);
                }
            };
            if ($addon !== null) {
                $f->setValues($orm->eventAddons->get($addon)->getValues());
            }
            $this->addComponent($f, 'form');
        };
    }

    public function render()
    {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->render();
    }
}
