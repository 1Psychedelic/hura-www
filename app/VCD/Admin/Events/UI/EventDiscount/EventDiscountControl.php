<?php

namespace VCD\Admin\Events\UI;

use Hafo\DI\Container;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nextras\Dbal\ForeignKeyConstraintViolationException;
use Tomaj\Form\Renderer\BootstrapRenderer;
use VCD2\Discounts\Discount;
use VCD2\Orm;

class EventDiscountControl extends Control
{
    public function __construct(Container $container, $event, $discount = null)
    {
        $orm = $container->get(Orm::class);
        $this->onAnchor[] = function () use ($event, $discount, $orm) {
            $f = new Form;
            $f->getElementPrototype()->addAttributes(['autocomplete' => 'off']);
            $f->setRenderer(new BootstrapRenderer);
            $f->addText('price', 'Nová cena')->setRequired();
            $f->addText('priceVip', 'Nová cena pro VIP')->setRequired();
            $f->addDateTimePicker('starts', 'Platnost od')->setRequired();
            $f->addDateTimePicker('ends', 'Platnost do')->setRequired();
            $f->addCheckbox('isDiscount', 'Sleva');
            $f->addCheckbox('allowDiscountCodes', 'Povolit slevové kódy');
            $f->addCheckbox('allowSiblingDiscount', 'Povolit sourozeneckou slevu');
            $f->addCheckbox('allowCredits', 'Povolit platbu kreditem');
            $f->addProtection();
            $f->addSubmit('save', 'Uložit');
            if ($discount !== null) {
                $f->addSubmit('delete', 'Odstranit');
            }
            $f->onSuccess[] = function (Form $f) use ($event, $discount, $orm) {
                if ($f->isSubmitted() === $f['save']) {
                    $data = $f->getValues(true);
                    $discountEntity = $discount === null
                        ? new Discount($orm->events->get($event), $data['starts'], $data['ends'], (int)$data['price'], (int)$data['priceVip'], (bool)$data['isDiscount'])
                        : $orm->discounts->get($discount);
                    if ($discountEntity->isPersisted()) {
                        $discountEntity->setValues($data);
                    }
                    $orm->persistAndFlush($discountEntity);
                    $this->presenter->flashMessage('Sleva uložena.', 'success');
                    $this->presenter->redirect('eventDiscounts', ['event' => $event]);
                } elseif ($discount !== null && $f->isSubmitted() === $f['delete']) {
                    try {
                        $orm->remove($orm->discounts->get($discount));
                    } catch (ForeignKeyConstraintViolationException $e) {
                        $this->presenter->flashMessage('Slevu nejde smazat, protože již byla použita v nějaké přihlášce.', 'danger');
                        $this->presenter->redirect('this');
                    }
                    $orm->flush();
                    $this->presenter->flashMessage('Sleva odstraněna.', 'success');
                    $this->presenter->redirect('eventDiscounts', ['event' => $event]);
                }
            };
            if ($discount !== null) {
                $f->setValues($orm->discounts->get($discount)->getValues());
            } else {
                $eventEntity = $orm->events->get($event);
                $f->setValues([
                    'price' => $eventEntity->priceBeforeDiscount,
                    'priceVip' => $eventEntity->priceVip,
                    'isDiscount' => true,
                    'allowDiscountCodes' => true,
                    'allowSiblingDiscount' => true,
                    'allowCredits' => true,
                ]);
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
