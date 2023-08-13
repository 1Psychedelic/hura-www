<?php

namespace VCD\Admin\Events\UI;

use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Utils\Arrays;
use VCD2\Events\Event;
use VCD2\Events\EventAddon;
use VCD2\FlashMessageException;
use VCD2\Orm;
use VCD2\UI\Admin\Forms\AdminFormRenderer;

class EventControl extends Control
{
    private $container;

    public function __construct(ContainerInterface $container, $id = null)
    {
        $this->container = $container;

        $orm = $this->container->get(Orm::class);

        $this->onAnchor[] = function () use ($id, $orm) {

            $emails = $orm->emails->findAll()->fetchPairs('id', 'name');

            $notImplemented = [
                'data-toggle' => 'tooltip',
                'title' => 'Není naimplementováno',
                'readonly' => 'readonly',
            ];

            $f = new Form;
            $f->getElementPrototype()->addAttributes(['autocomplete' => 'off']);
            $f->setRenderer(new AdminFormRenderer);

            $f->addGroup('Základní údaje');
            $f->addText('name', 'Název')->setRequired();
            $f->addText('subheading', 'Text nad nadpisem')->setNullable();
            $f->addSelect('type', 'Druh', Event::TYPES_NAMES)->setValue(Event::TYPE_TRIP);
            $f->addCheckbox('visible', 'Viditelný');
            $f->addXSelect('acceptedEmail', 'Šablona e-mailu "přihláška schválena"', $emails)->setPrompt('(Žádná šablona)');
            $f->addTextArea('description', 'Krátký popis (10-15 slov)')->setRequired();
            $f->addTextArea('sidebarHtml', 'Sidebar HTML');

            $f->addGroup('Termíny');
            $f->addDateTimePicker('applicableUntil', 'Uzavírka přihlášek')->setValue(new \DateTime)->setRequired();
            $f->addDateTimePicker('starts', 'Odjezd')->setValue(new \DateTime)->setRequired();
            $f->addDateTimePicker('ends', 'Příjezd')->setValue(new \DateTime)->setRequired();

            $f->addGroup('Kapacita');
            //$f->addText('participants', 'Počet přijatých dětí')->setType('number');
            $f->addText('changeParticipants', 'Zmanipulovat počet přijatých dětí')->setType('number')->getControlPrototype()->addAttributes([
                'min' => -100,
                'max' => 100,
            ]);
            $f->addText('maxParticipants', 'Kapacita')->setType('number')->setValue(29)->setRequired();
            $f->addText('maxReserves', 'Náhradníci')->setType('number')->setValue(10)->setRequired();

            $f->addGroup('Věk');
            $f->addText('ageMin', 'Minimální věk')->setType('number')->setValue(5)->setRequired();
            $f->addText('ageMax', 'Maximální doporučený věk')->setType('number')->setValue(14)->setRequired();
            $f->addText('ageCap', 'Maximální věk')->setType('number')->setValue(14)->setRequired();

            $f->addGroup('Omezení');
            $users = []; //$orm->users->findSelectOptions();
            $f->addTags('visibleForUsers', 'Pouze pro vybrané', $users)
                ->setDisabled()
                ->getControlPrototype()
                ->addAttributes($notImplemented);
            $f->addCheckbox('openForVip', 'Pouze pro VIP')
                ->setDisabled()
                ->getControlPrototype()
                ->addAttributes($notImplemented);

            $f->addGroup('Cena');
            $f->addText('priceBeforeDiscount', 'Cena')->setType('number')->setRequired();
            $f->addText('priceVip', 'Cena VIP')->setType('number')->setNullable();

            if ($id === null) {
                $f['priceBeforeDiscount']->setRequired();
            } else {
                //$f['priceBeforeDiscount']->setDisabled();
                //$f['priceVip']->setDisabled();

                $attrs = [
                    'data-toggle' => 'tooltip',
                    'title' => 'Pro změnu ceny použij záložku "Cena".',
                    'readonly' => 'readonly',
                ];
                $f['priceVip']->getControlPrototype()->addAttributes($attrs);
                $f['priceBeforeDiscount']->getControlPrototype()->addAttributes($attrs);
            }

            $f->addText('deposit', 'Záloha')->setType('number')->setRequired();
            $f->addText('siblingDiscount', 'Sourozenecká sleva')->setType('number')->setValue(0)->setRequired()
                ->getControlPrototype()
                ->addAttributes($notImplemented);

            $f->addGroup('Schema.org');
            $f->addText('schemaLocationName', 'Název místa')->setNullable();
            $f->addText('schemaLocationAddressLocality', 'Adresa místa')->setNullable();
            $f->addText('schemaLocationAddressRegion', 'Region')->setNullable();
            $f->addText('schemaLocationAddressPostalCode', 'PSČ')->setNullable();

            $f->addGroup('Ostatní');
            $f->addTextArea('keywords', 'Klíčová slova oddělená čárkou');

            $f->setCurrentGroup(null);
            $f->addProtection();
            $f->addSubmit('save', 'Uložit');

            if ($id !== null) {
                $f->addSubmit('delete', 'Odstranit')->getControlPrototype()
                    ->attrs['onclick'] = 'if(!confirm("Opravdu smazat?")){return false;}';
            }

            $f->onSuccess[] = function (Form $f) use ($id, $orm) {
                if ($f->isSubmitted() === $f['save']) {
                    $data = $f->getValues(true);

                    $visibleForUsers = Arrays::pick($data, 'visibleForUsers', []);

                    try {
                        $event = $id === null ? Event::createFromArray($data, $orm->events) : $orm->events->get($id);

                        $event->setValues($data);
                    } catch (FlashMessageException $e) {
                        $this->presenter->flashMessage($e->getFlashMessage());

                        return;
                    }

                    $i = 0;
                    do {
                        if ($id === null || $i > 0) {
                            $event->generateSlug($i);
                        }
                        $existing = $orm->events->getBy(['slug' => $event->slug]);
                        if ($id !== null && $existing === $event) {
                            break;
                        }
                        $i++;
                    } while ($existing !== null);

                    $event->openForUsers->set($visibleForUsers);

                    $orm->persistAndFlush($event);

                    if ($id === null) {
                        $addonsConfig = $this->container->get('vcd_event.addons');
                        foreach ($addonsConfig as $addonConfig) {
                            $addon = new EventAddon($event);
                            $addon->enabled = $addonConfig['enabled'];
                            $addon->name = $addonConfig['name'];
                            $addon->price = $addonConfig['price'];
                            $addon->position = $addonConfig['position'];
                            $addon->description = $addonConfig['description'];
                            $addon->icon = $addonConfig['icon'];
                            $addon->linkUrl = $addonConfig['linkUrl'];
                            $addon->linkText = $addonConfig['linkText'];
                            $orm->persist($addon);
                        }
                        $orm->flush();
                    }

                    $this->presenter->redirect('events');
                } elseif ($id !== null && $f->isSubmitted() === $f['delete']) {
                    //try {
                    $orm->remove($orm->events->get($id));
                    /*    $this->presenter->flashMessage('Událost byla smazána.', 'success');
                        $this->presenter->redirect('events');
                    } catch (ForeignKeyConstraintViolationException $e) {
                        $this->presenter->flashMessage('Nepodařilo se odstranit událost, protože databáze obsahuje položky, které na ní závisí (např. přihlášky, slevové kódy).', 'danger');
                        $this->presenter->redirect('this');
                    }*/
                }
            };
            if ($id !== null) {
                $f->setValues($orm->events->get($id)->getValues());
                $f['visibleForUsers']->setValue($orm->events->get($id)->openForUsers->get()->fetchPairs('id', 'id'));
            }
            $this->addComponent($f, 'form');
            $this->template->id = $id;
        };
    }

    public function render()
    {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->render();
    }
}
