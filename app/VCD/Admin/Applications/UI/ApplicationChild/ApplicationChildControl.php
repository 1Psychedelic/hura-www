<?php

namespace VCD\Admin\Applications\UI;

use Hafo\Persona\Gender;
use Psr\Container\ContainerInterface;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Database\Context;
use Nette\Utils\Html;
use Nextras\Orm\Collection\ICollection;
use Tomaj\Form\Renderer\BootstrapRenderer;
use VCD2\Applications\Application;
use VCD2\Applications\Child;
use VCD2\FlashMessageException;
use VCD2\Orm;

class ApplicationChildControl extends Control {

    private $container;

    function __construct(ContainerInterface $container, $applicationId, $id = NULL) {
        $this->container = $container;

        $this->onAnchor[] = function() use ($applicationId, $id) {

            $orm = $this->container->get(Orm::class);

            /**
             * @var Application|NULL $application
             */
            $application = $orm->applications->get($applicationId);
            if($application === NULL) {
                throw new ForbiddenRequestException;
            }

            $children = [];
            if($application->user !== NULL) {
                $children = $application->user->children;
            } else {
                $children = $orm->children->findAll()->orderBy('id', ICollection::DESC);
            }

            $properties = ['name', 'dateBorn', 'gender', 'swimmer', 'adhd', 'health', /*'allergy', */'notes'];
            $m = $id === NULL ? 'replace' : 'fill';
            $childrenOptions = [];
            foreach($children as $child) {
                $dataInner = [];
                foreach($properties as $property) {
                    $dataInner[$property] = $child->$property instanceof \DateTimeInterface ? $child->$property->format('Y-m-d H:i:s') : $child->$property;
                }
                $childrenOptions[$child->id] = Html::el(NULL, [
                    "data-$m" => $dataInner
                ])->setText('#' . $child->id . ' ' . $child->name);
            }
            $f = new Form;
            $f->setRenderer(new BootstrapRenderer);
            $f->addXSelect('child', 'Dítě', $childrenOptions)->setPrompt('(Neregistrované)');
            $f->addText('name', 'Jméno a příjmení');
            //$f->addText('personalId', 'Rodné číslo')
            //    ->addCondition(Form::FILLED)
            //    ->addRule('\Hafo\NetteBridge\Forms\Validators\CzechPersonalIdValidator::validate', 'Zadaná hodnota nevypadá jako platné rodné číslo.');
            $f->addDateTimePicker('dateBorn', 'Datum narození');
            $f->addRadioList('gender', 'Pohlaví', [
                Gender::MALE => 'Chlapec',
                Gender::FEMALE => 'Dívka',
            ]);
            $f->addCheckbox('swimmer', 'Plavec');
            $f->addCheckbox('adhd', 'ADHD');
            $f->addTextArea('health', 'Zdravotní stav');
            //$f->addTextArea('allergy', 'Alergie');
            $f->addTextArea('notes', 'Poznámka');
            $f->addProtection();
            $f->addSubmit('save', 'Uložit');
            if($id !== NULL) {
                $f->addSubmit('delete', 'Smazat')->getControlPrototype()
                    ->attrs['onclick'] = 'if(!confirm("Opravdu smazat?")){return false;}';
            }
            $f->onSuccess[] = function(Form $f) use ($orm, $application, $id) {
                if($f->isSubmitted() === $f['save']) {
                    $data = $f->getValues(TRUE);

                    Child::$validateEventAgeRestriction = FALSE;

                    /** @var Child $applicationChild */
                    if($id === NULL) {
                        try {
                            $applicationChild = Child::createFromArray($application, $data['child'] === NULL ? NULL : $orm->children->get($data['child']), $data);
                            $application->recalculatePrice();
                            $orm->persist($application);
                            $orm->persistAndFlush($applicationChild);
                            $this->presenter->flashMessage('Uloženo.', 'success');
                            $this->presenter->redirect('this');
                        } catch (FlashMessageException $e) {
                            $this->presenter->flashMessage($e->getFlashMessage());
                            return;
                        }
                    } else {
                        $applicationChild = $orm->applicationChildren->get($id);
                        try {
                            $applicationChild->updateInfo(
                                $data['name'],
                                $data['gender'],
                                new \DateTimeImmutable($data['dateBorn']->format('Y-m-d H:i:s')),
                                $data['swimmer'],
                                $data['adhd'],
                                $data['health'],
                                NULL,
                                $data['notes']
                            );
                        } catch (FlashMessageException $e) {
                            $this->presenter->flashMessage($e->getFlashMessage());
                            return;
                        }
                        $application->recalculatePrice();
                        $orm->persist($application);
                        $orm->persistAndFlush($applicationChild);
                        $this->presenter->flashMessage('Uloženo.', 'success');
                        $this->presenter->redirect('this');
                    }
                } else if($id !== NULL && $f->isSubmitted() === $f['delete']) {
                    $orm->remove($orm->applicationChildren->get($id));
                    $application->recalculatePrice();
                    $orm->persist($application);
                    $orm->flush();
                    $this->presenter->flashMessage('Dítě bylo smazané z přihlášky.', 'success');
                    $this->presenter->redirect('applications', ['filters' => ['status' => ApplicationsFiltersControl::STATUS_NEW]]);
                }
            };
            if($id !== NULL) {
                $applicationChild = $orm->applicationChildren->get($id);
                $f->setValues($applicationChild->getValues());
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
