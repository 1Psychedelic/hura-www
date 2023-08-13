<?php

namespace VCD\UI\FrontModule\UserModule;

use Hafo\DI\Container;
use Hafo\NetteBridge\Forms\FormFactory;
use Hafo\Persona\Gender;
use Haltuf\Genderer\Genderer;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\InvalidStateException;
use Nette\Utils\Arrays;
use Tomaj\Form\Renderer\BootstrapRenderer;
use VCD\Users;
use VCD2\FlashMessageException;
use VCD2\Orm;
use VCD2\Users\Child;
use VCD2\Users\Service\UserContext;

class ProfileChildControl extends Control
{

    public $onSave = [];

    private $child;

    function __construct(Container $container, $id = NULL)
    {
        $orm = $container->get(Orm::class);
        $user = $container->get(UserContext::class)->getEntity();

        if($user === NULL) {
            throw new InvalidStateException;
        }

        if($id !== NULL) {
            $this->child = $orm->children->get($id);
        }

        $f = $container->get(FormFactory::class)->create();
        $f->setRenderer(new BootstrapRenderer);
        if($id === NULL || $this->child->isEditableByUser) {
            $f->addText('name', 'Jméno a příjmení dítěte')->setRequired()->addRule(Form::PATTERN, 'Zadejte prosím jméno a příjmení dítěte', '(.*)\s(.*)');
            $f->addDate('dateBorn', 'Datum narození')->setRequired();
            $f->addRadioList('gender', 'Pohlaví', [
                Gender::MALE => 'Chlapec',
                Gender::FEMALE => 'Dívka',
            ]);
        }
        $f->addRadioList('swimmer', 'Plavec/neplavec', [
            FALSE => 'Neplavec',
            TRUE => 'Plavec'
        ])->setRequired('Prosím uveďte zda dítě umí plavat.');
        $f->addRadioList('adhd', 'Má vaše dítě ADHD nebo podobnou diagnozu?', [
            0 => 'Ne',
            1 => 'Ano',
        ])->setRequired('Prosím uveďte zda má dítě ADHD nebo podobnou diagnózu.');
        //$f->addTextArea('allergy', 'Alergie')->setRequired('Prosím uveďte zda má dítě nějaké alergie, nebo napište "žádné" pokud žádné nemá.');
        $f->addTextArea('health', 'Zdravotní stav')/*->setRequired('Prosím popište zdravotní stav dítěte.')*/;
        $f->addTextArea('notes', 'Poznámka');
        $f->addSubmit('save', 'Uložit');
        $f->addSubmit('back', 'Jít zpět')->setValidationScope(FALSE);
        $f->onSuccess[] = function (Form $f) use ($orm, $id, $user) {
            if ($f->isSubmitted() === $f['save']) {
                $data = $f->getValues(TRUE);

                try {
                    /** @var Child $userChild */
                    if($id === NULL) {
                        $userChild = Child::createFromArray($user, $data);
                    } else {
                        $userChild = $user->children->get()->getBy(['id' => $id]);
                        $userChild->updateInfo(
                            $userChild->isEditableByUser ? $data['name'] : $userChild->name,
                            $userChild->isEditableByUser ? $data['gender'] : $userChild->gender,
                            $userChild->isEditableByUser ? new \DateTimeImmutable($data['dateBorn']) : $userChild->dateBorn,
                            $data['swimmer'],
                            $data['adhd'],
                            $data['health'],
                            NULL,
                            $data['notes']
                        );
                    }

                    $orm->persistAndFlush($userChild);

                    $this->presenter->flashMessage('Údaje byly uloženy.', 'success');
                    $this->presenter->redirect(ChildPresenter::LINK_DEFAULT, ['id' => $userChild->getPersistedId()]);
                } catch (FlashMessageException $e) {
                    $this->presenter->flashMessage($e->getFlashMessage());
                    return;
                }
            } else if ($f->isSubmitted() === $f['back']) {
                if ($id === NULL) {
                    $this->presenter->redirect(ProfilePresenter::LINK_DEFAULT);
                } else {
                    $this->presenter->redirect(ChildPresenter::LINK_DEFAULT, ['id' => $id]);
                }
            }
        };
        if ($id !== NULL) {
            $userChild = $user->children->get()->getBy(['id' => $id]);
            if($userChild === NULL) {
                throw new InvalidStateException;
            }
            $data = $userChild->getValues();

            // fucking bug workaround
            $swimmer = (int)Arrays::pick($data, 'swimmer');
            $f['swimmer']->setValue($swimmer);
            $adhd = (int)Arrays::pick($data, 'adhd');
            $f['adhd']->setValue($adhd);
            $data['dateBorn'] = $data['dateBorn']->format('Y-m-d');

            $f->setValues($data);
        }
        $this->addComponent($f, 'form');
    }

    function handleDetermineGender($name) {
        $genderer = new Genderer;
        $gender = $genderer->getGender($name);
        $this->presenter->sendJson((object)[
            'gender' => $gender,
        ]);
    }

    function render()
    {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->child = $this->child;
        $this->template->render();
    }

}
