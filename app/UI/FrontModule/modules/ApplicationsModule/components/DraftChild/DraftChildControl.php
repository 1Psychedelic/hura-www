<?php

namespace VCD\UI\FrontModule\ApplicationsModule;

use Hafo\DI\Container;
use Hafo\NetteBridge\Forms\FormFactory;
use Hafo\Persona\Gender;
use Hafo\Persona\HumanAge;
use Haltuf\Genderer\Genderer;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Utils\Arrays;
use Nette\Utils\Html;
use VCD\Users;
use VCD2\Applications;
use VCD2\Applications\Application;
use VCD2\Applications\Service\Drafts;
use VCD2\FlashMessageException;
use VCD2\Orm;
use VCD2\Users\Child;
use VCD2\Users\Service\UserContext;

/**
 * @method onSave()
 */
class DraftChildControl extends Control {

    public $onSave = [];

    /** @var Applications\Child|NULL */
    private $draftChild;

    function __construct(Container $container, Application $draft, $child = NULL) {
        $user = $container->get(UserContext::class)->getEntity();
        $orm = $container->get(Orm::class);
        $drafts = $container->get(Drafts::class);

        if($child !== NULL) {
            $this->draftChild = $orm->applicationChildren->get($child);
        }

        /** @var Form $f */
        $f = $container->get(FormFactory::class)->create();

        if($child === NULL || $this->draftChild->child->isEditableByUser) {
            $f->addText('name', 'Jméno a příjmení dítěte')
                ->setRequired()
                ->addRule(Form::PATTERN, 'Zadejte prosím jméno a příjmení dítěte', '(.*)\s(.*)');

            $f->addDate('dateBorn', 'Datum narození')
                ->setRequired('Zadejte prosím datum narození dítěte');

            $f->addRadioList('gender', 'Pohlaví', [
                Gender::MALE => 'Chlapec',
                Gender::FEMALE => 'Dívka',
            ])->setRequired('Prosím vyberte pohlaví dítěte');
        }

        $f->addRadioList('swimmer', 'Plavec/neplavec', [
            0 => 'Neplavec',
            1 => 'Plavec',
        ])->setRequired('Prosím uveďte zda dítě umí plavat.');

        $f->addRadioList('adhd', 'Má vaše dítě ADHD nebo podobnou diagnozu?', [
            0 => 'Ne',
            1 => 'Ano',
        ])->setRequired('Prosím uveďte zda má dítě ADHD nebo podobnou diagnózu.');

        $f->addTextArea('health', 'Zdravotní stav');

        $f->addTextArea('notes', 'Poznámka');

        /*if($user !== NULL) {
            $f->addCheckbox('saveProfile', 'Uložit informace o dítěti do mého seznamu pro usnadnění budoucího přihlašování');
        }*/

        $f->addCheckbox('truth', 'Závazně prohlašuji, že údaje vyplněné v přihlášce odpovídají skutečnosti.')
            ->setRequired('Prosím odsouhlaste že vyplněné údaje odpovídají skutečnosti.');

        $f->addButtonSubmit('save', Html::el()->addHtml(Html::el('span')->class('glyphicon glyphicon-ok'))->addHtml(' Uložit a přidat do přihlášky'));
        $f->addSubmit('back', 'Jít zpět')->setValidationScope(FALSE);

        $f->onSuccess[] = function(Form $f) use ($orm, $user, $draft, $child, $drafts) {
            if($f->isSubmitted() === $f['save']) {
                $data = $f->getValues(TRUE);
                /**
                 * @var Child|NULL $userChild
                 * @var \VCD2\Applications\Child|NULL $draftChild
                 */
                $userChild = NULL;
                $draftChild = $child === NULL ? NULL : $draft->children->get()->getBy(['id' => $child]);

                if($draftChild !== NULL && $draftChild->child !== NULL && !$draftChild->child->isEditableByUser) {
                    $data['dateBorn'] = $draftChild->child->dateBorn;
                    $data['name'] = $draftChild->child->name;
                    $data['gender'] = $draftChild->child->gender;
                } else {
                    $data['dateBorn'] = new \DateTimeImmutable($data['dateBorn']);
                }

                // save profile
                if($user !== NULL) {
                    $ok = TRUE;
                    if($child === NULL || $draftChild->child === NULL) {
                        try {
                            $userChild = Child::createFromArray($user, $data);
                        } catch (FlashMessageException $e) {
                            $this->presenter->flashMessage($e->getFlashMessage());
                            $ok = FALSE;
                        }
                    } else {
                        $userChild = $draftChild->child;
                        try {
                            $userChild->updateInfo(
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
                            $ok = FALSE;
                        }
                    }
                    if($ok && $userChild !== NULL) {
                        $orm->persist($userChild);
                    } else {
                        return;
                    }
                }

                // save child
                if($draftChild === NULL) {
                    try {
                        $draftChild = \VCD2\Applications\Child::createFromArray($draft, $userChild, $data);
                    } catch (FlashMessageException $e) {
                        $this->presenter->flashMessage($e->getFlashMessage());
                        return;
                    }
                } else {
                    $draftChild->child = $userChild;
                    try {
                        $draftChild->updateInfo(
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
                }

                if($f->hasErrors()) {
                    $this->parent->flashMessage('Došlo k chybě při odesílání formuláře. Prosím zkontrolujte zadané údaje.', 'danger');
                    return;
                }

                $orm->persist($draftChild);
                $drafts->saveDraft($draft);

                // doporučený věk
                if(!$draftChild->isWithinRecommendedAge) {
                    $age = (new HumanAge($draftChild->dateBorn))->yearsAt($draft->event->ends);
                    $this->parent->flashMessage(sprintf(
                        'Věk vašeho dítěte %s je vyšší než doporučený věk pro tuto akci. Přihlášku můžete odeslat, upozorňujeme však, že akce je určena primárně mladším dětem.',
                        $age
                    ), 'warning');
                }

                $this->onSave();
            } else if($f->isSubmitted() === $f['back']) {
                $this->onSave();
            }
        };
        if($child !== NULL) {
            $draftChild = $draft->children->get()->getBy(['id' => $child]);
            $data = $draftChild->getValues(['name', 'dateBorn', 'gender', 'swimmer', 'adhd', 'health', 'notes']);
            $data['dateBorn'] = $data['dateBorn']->format('Y-m-d');

            // failsafe
            /*if(!in_array($data['insurance'], array_keys($insuranceCompanies))) {
                unset($data['insurance']);
            }*/

            // fucking bug workaround
            $swimmer = (int)Arrays::pick($data, 'swimmer');
            $f['swimmer']->setValue($swimmer);
            $adhd = (int)Arrays::pick($data, 'adhd');
            $f['adhd']->setValue($adhd);

            $f->setValues($data);
            /*if($draftChild->child !== NULL) {
                $f['saveProfile']->setValue(TRUE);
            }*/
        }/* else if(isset($f['saveProfile'])) {
            $f['saveProfile']->setValue(TRUE);
        }*/
        $this->addComponent($f, 'form');
    }

    function handleDetermineGender($name) {
        $genderer = new Genderer;
        $gender = $genderer->getGender($name);
        $this->presenter->sendJson((object)[
            'gender' => $gender,
        ]);
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->draftChild = $this->draftChild;
        $this->template->render();
    }

}
