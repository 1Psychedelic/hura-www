<?php

namespace VCD\Admin\Applications\UI;

use Hafo\Utils\Arrays;
use Psr\Container\ContainerInterface;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Database\Context;
use Nette\Utils\Html;
use Nextras\Orm\Collection\ICollection;
use VCD2\Applications\Application;
use VCD2\Applications\Child;
use VCD2\Applications\StepChoice;
use VCD2\Discounts\DiscountCode;
use VCD2\Events\Event;
use VCD2\FlashMessageException;
use VCD2\Orm;
use VCD2\UI\Admin\Forms\AdminFormRenderer;
use VCD2\Users\User;

class ApplicationControl extends Control {

    private $container;

    function __construct(ContainerInterface $container, $id = NULL, $duplicate = NULL) {
        $this->container = $container;

        $this->onAnchor[] = function() use ($id, $duplicate) {

            $orm = $this->container->get(Orm::class);

            /**
             * @var Application|NULL $application
             * @var User[] $users
             * @var DiscountCode[] $discounts
             */
            $application = $id === NULL ? NULL : $orm->applications->get($id);

            $users = $orm->users->findAll()->orderBy('id', ICollection::DESC);
            $discounts = $orm->discountCodes->findAll()->orderBy('id', ICollection::DESC);
            $discountOptions = [];
            foreach($discounts as $discount) {
                $discountOptions[$discount->id] = sprintf('%s (%d Kč)', $discount->code, $discount->discount);
            }

            $m = $id === NULL ? 'replace' : 'fill';

            $properties = ['name', 'email', 'phone', 'city', 'street', 'zip', 'agreedPersonalData', 'agreedPhotography'];
            $userOptions = [];
            foreach($users as $user) {
                $dataInner = [];
                foreach($properties as $property) {
                    $dataInner[$property] = $user->$property;
                }
                $userOptions[$user->id] = Html::el(NULL, [
                    "data-$m" => $dataInner
                ])->setText($user->name . ', ' . $user->email);
            }
            $f = new Form;
            $f->setRenderer(new AdminFormRenderer);

            $f->addGroup('Základní údaje');

            if ($id === null) {
                $eventOptions = $orm->events->findSelectOptionsForAdmin();
                $f->addXSelect('event', 'Událost', $eventOptions);
            } else {
                $f->addSelect('event', 'Událost', [
                    $application->event->id => '#' . $application->event->id . ' ' . $application->event->name,
                ])->setDisabled(true);
            }

            $f->addXSelect('user', 'Uživatel', $userOptions)->setPrompt('(Neregistrovaný)');
            $f->addXSelect('discountCode', 'Slevový kód', $discountOptions)->setPrompt('(Žádný)');
            //$f->addText('price', 'Cena Kč');
            //$f->addText('deposit', 'Záloha');
            //$f->addText('paid', 'Zaplaceno Kč');

            $f->addGroup('Zákonný zástupce');
            $f->addText('name', 'Jméno a příjmení zákonného zástupce')->setRequired();
            $f->addText('email', 'E-mail')->addCondition(Form::FILLED)->addRule(Form::EMAIL);
            $f->addText('phone', 'Telefon');
            $f->addText('city', 'Město');
            $f->addText('street', 'Ulice');
            $f->addText('zip', 'PSČ');
            $f->addCheckbox('agreedPersonalData', 'Souhlas se zpracováním osobních údajů');
            $f->addCheckbox('agreedTermsAndConditions', 'Souhlas se smluvními podmínkami');
            $f->addCheckbox('agreedPhotography', 'Souhlas s pořizováním snímků');

            $f->addGroup('Příznaky');
            $f->addCheckbox('isReserve', 'Náhradník');
            $f->addCheckbox('isPayingOnInvoice', 'Úhrada zaměstnavatelem')
                ->addCondition(Form::EQUAL, TRUE)
                    ->toggle('invoice');
            $f->addSelect('vipLevel', 'Cenové zvýhodnění', [
                0 => 'Žádné',
                1 => 'VIP cena',
                2 => 'Zdarma',
            ]);

            $f->addGroup('Zaměstnavatel')
                ->setOption('id', 'invoice');
            $f->addText('invoiceName', 'Název společnosti');
            $f->addText('invoiceIco', 'IČO');
            $f->addText('invoiceDic', 'DIČ');
            $f->addText('invoiceCity', 'Město');
            $f->addText('invoiceStreet', 'Ulice');
            $f->addText('invoiceZip', 'PSČ');


            $f->addGroup('Stav');
            $f->addDateTimePicker('appliedAt', 'Odesláno')->setValue(new \DateTime);
            //$f->addDateTimePicker('paidAt', 'Zaplaceno');
            $f->addDateTimePicker('acceptedAt', 'Schváleno');
            $f->addDateTimePicker('rejectedAt', 'Odmítnuto');
            $f->addDateTimePicker('canceledAt', 'Zrušeno');

            if($application !== NULL && $application->event !== NULL && $application->event->steps->count() > 0) {
                $f->addGroup('Mezikroky');
                foreach($application->event->steps as $step) {
                    $options = [];
                    foreach($step->options as $option) {
                        $options[$option->id] = $option->option;
                    }
                    $f->addRadioList('step_' . $step->id, $step->prompt, $options);
                }
            }

            $f->addGroup('Ostatní');
            $f->addTextArea('notes', 'Poznámka')->setNullable();

            if($duplicate !== NULL) {
                $f->addGroup('Děti');
                /** @var Application $duplicateApplication */
                $duplicateApplication = $orm->applications->get($duplicate);
                $children = $duplicateApplication->children->get()->fetchPairs('id', 'name');
                $f->addCheckboxList('duplicate_children', 'Zkopírovat tyto děti', $children);
            }

            //$r = array_intersect_key($_POST, array_flip(preg_grep('/^step_/', array_keys($_POST))));
            $f->setCurrentGroup(NULL);
            $f->addProtection();
            $f->addSubmit('save', $duplicate === NULL ? 'Uložit' : 'Zkopírovat');
            if($application !== NULL) {
                $f->addSubmit('delete', 'Smazat')->getControlPrototype()
                    ->attrs['onclick'] = 'if(!confirm("Opravdu smazat?")){return false;}';
            }
            $f->onSuccess[] = function(Form $f) use ($orm, $application, $duplicate) {
                if($f->isSubmitted() === $f['save']) {

                    $data = $f->getValues(TRUE);
                    $steps = Arrays::subArrayByPrefix($data, 'step_', TRUE, TRUE);

                    if($application === NULL) {
                        
                        $children = \Nette\Utils\Arrays::pick($data, 'duplicate_children', []);
                        
                        // create and persist a new application
                        $newApplication = new Application(
                            $orm->events->get($data['event']),
                            $data['user'] === NULL ? NULL : $orm->users->get($data['user'])
                        );
                        $newApplication->setValues($data);
                        $orm->persist($newApplication);
                        
                        // duplicate children
                        if($duplicate !== NULL) {
                            /** @var Child[] $childrenEntities */
                            $childrenEntities = $orm->applicationChildren->find($children);
                            foreach($childrenEntities as $child) {
                                try {
                                    $newChild = Child::createFromApplicationChild($newApplication, $child);
                                } catch (FlashMessageException $e) {
                                    $this->presenter->flashMessage($e->getFlashMessage());
                                    return;
                                }
                                $orm->persist($newChild);
                            }
                        }

                        // save
                        $newApplication->recalculatePrice();
                        $orm->persist($newApplication);
                        $orm->flush();

                        $this->presenter->flashMessage('Uloženo.', 'success');
                        $this->presenter->redirect('applications', ['past' => TRUE, 'filters' => ['id' => $newApplication->id]]);

                    } else {

                        $application->setValues($data);

                        // remove invalid steps
                        foreach($application->stepChoices as $stepChoice) {
                            $orm->remove($stepChoice);
                        }

                        // save selected steps
                        foreach($steps as $step => $option) {
                            $stepChoice = $orm->applicationStepChoices->getBy(['application' => $application, 'step' => $step]);
                            if($stepChoice === NULL) {
                                $stepChoice = new StepChoice($application, $orm->eventStepOptions->get($option));
                            }
                            $orm->persist($stepChoice);
                        }

                        $application->recalculatePrice();
                        $orm->persist($application);
                        $orm->flush();

                        $this->presenter->flashMessage('Uloženo.', 'success');
                    }
                    $this->presenter->redirect('this');

                } else if ($application !== NULL && $f->isSubmitted() === $f['delete']) {

                    $orm->remove($application);
                    $orm->flush();

                    //$event = $application->event;
                    //if($event !== NULL) {
                    //    $this->container->get(ParticipantsChanged::class)->update($event->id);
                    //}

                    $this->presenter->flashMessage('Přihláška byla smazána.', 'success');
                    $this->presenter->redirect('applications', ['filters' => ['status' => ApplicationsFiltersControl::STATUS_NEW]]);
                }
            };

            if($id !== NULL || $duplicate !== NULL) {

                /** @var Application|NULL $template */
                $template = $orm->applications->get($id === NULL ? $duplicate : $id);
                if($template === NULL) {
                    throw new ForbiddenRequestException;
                }
                $f->setValues($template->getValues());

                // fill steps
                foreach($template->stepChoices as $stepChoice) {
                    if(isset($f['step_' . $stepChoice->step->id])) {
                        $f['step_' . $stepChoice->step->id]->setValue($stepChoice->option->id);
                    }
                }

                if($duplicate !== NULL) {
                    $f['duplicate_children']->setValue($template->children->get()->fetchPairs(NULL, 'id'));
                }
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
