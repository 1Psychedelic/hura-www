<?php

namespace VCD\Admin\Discounts\UI;

use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Database\Context;
use Nette\Utils\Arrays;
use Nextras\Dbal\UniqueConstraintViolationException;
use Nextras\Orm\Collection\ICollection;
use Tomaj\Form\Renderer\BootstrapRenderer;
use VCD2\Discounts\DiscountCode;
use VCD2\Orm;

class DiscountControl extends Control {

    private $container;

    function __construct(ContainerInterface $container, $id = NULL) {
        $this->container = $container;

        $this->onAnchor[] = function() use ($id) {
            
            $orm = $this->container->get(Orm::class);

            /** @var DiscountCode|NULL $discount */
            $discount = $id === NULL ? NULL : $orm->discountCodes->get($id);
            
            $userOptions = [];
            $users = $orm->users->findAll();
            foreach($users as $user) {
                $userOptions[$user->id] = sprintf('%s, %s', $user->name, $user->email);
            }

            $eventsSelection = $id === NULL ? $orm->events->findUpcoming() : $orm->events->findAll();
            $eventsSelection = $eventsSelection->orderBy('ends', ICollection::DESC);
            $eventOptions = $eventsSelection->fetchPairs('id', 'name');

            $f = new Form;
            $f->setRenderer(new BootstrapRenderer);
            $f->addText('code', 'Kód')
                ->setRequired()
                ->addFilter(function($val) {return strtoupper($val);});
            $f->addText('discount', 'Hodnota')
                ->setType('number')
                ->setRequired();
            //$f->addText('usagesLeft', 'Zbývá použití')->setType('number')->getControlPrototype()->setAttribute('placeholder', '(Neomezeno)');
            $f->addText('maxUsages', 'Maximum použití')
                ->setNullable(TRUE)
                ->setType('number')
                ->getControlPrototype()
                    ->setAttribute('placeholder', '(Neomezeno)');
            $f->addXSelect('forUser', 'Pouze pro uživatele', $userOptions)
                ->setPrompt('(Neomezeno)');
            $f->addXMultiSelect('forEvents', 'Pouze pro události', $eventOptions, '(Neomezeno)', FALSE, TRUE);
            $f->addDateTimePicker('expires', 'Expirace')
                ->setNullable(TRUE)
                ->setValue(new \DateTime)
                ->getControlPrototype()
                    ->setAttribute('placeholder', '(Neomezeno)');
            $f->addCheckbox('multiplyByChildren', 'Vynásobit slevu počtem dětí v přihlášce');
            $f->addSubmit('save', 'Uložit');
            if($discount !== NULL) {
                $f->addSubmit('delete', 'Odstranit')->getControlPrototype()
                    ->attrs['onclick'] = 'if(!confirm("Opravdu smazat?")){return false;}';
            }

            $f->onSuccess[] = function(Form $f) use ($orm, $discount) {
                if($f->isSubmitted() === $f['save']) {
                    $data = $f->getValues(TRUE);
                    //$data['usagesLeft'] = $data['usagesLeft'] === '' ? NULL : $data['usagesLeft'];
                    $events = Arrays::pick($data, 'forEvents');

                    if($discount === NULL) {
                        $discount = new DiscountCode($data['code'], $data['discount'], $data['maxUsages'], DiscountCode::SOURCE_MANUAL);
                        $orm->discountCodes->attach($discount);
                    }

                    $discount->setValues($data);
                    $discount->forEvents->set($events);

                    $orm->persistAndFlush($discount);

                    $this->presenter->flashMessage('Uloženo.', 'success');
                    $this->presenter->redirect('discounts');
                } else if($discount !== NULL && $f->isSubmitted() === $f['delete']) {

                    try {
                        $orm->remove($discount);
                    } catch (UniqueConstraintViolationException $e) {
                        $this->presenter->flashMessage('Slevový kód nejde smazat, protože již byl použit v nějaké přihlášce.', 'danger');
                        $this->presenter->redirect('this');
                    }
                    $orm->flush();

                    $this->presenter->flashMessage('Slevový kód byl smazán.', 'success');
                    $this->presenter->redirect('discounts');
                }
            };
            if($discount !== NULL) {
                $f->setValues($discount->getValues());
                $f['forEvents']->setValue($discount->forEvents->getRawValue());
            }
            $this->addComponent($f, 'form');
        };
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->render();
    }

    private function db() {
        return $this->container->get(Context::class);
    }

}
