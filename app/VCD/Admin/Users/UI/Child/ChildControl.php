<?php

namespace VCD\Admin\Users\UI;

use Hafo\Persona\Gender;
use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Utils\Arrays;
use Tomaj\Form\Renderer\BootstrapRenderer;
use VCD2\FlashMessageException;
use VCD2\Orm;
use VCD2\Users\Child;

class ChildControl extends Control {

    private $container;

    function __construct(ContainerInterface $container, $id = NULL) {
        $this->container = $container;

        $this->onAnchor[] = function() use ($id) {

            $orm = $this->container->get(Orm::class);

            $users = [];
            foreach($orm->users->findAll() as $user) {
                $users[$user->id] = sprintf('#%s %s, %s %s', $user->id, $user->name, $user->email, empty($user->phone) ? '' : sprintf('(%s)', $user->phone));
            }

            $f = new Form;
            $f->setRenderer(new BootstrapRenderer);
            $f->addText('name', 'Jméno a příjmení');
            $f->addXMultiSelect('parents', 'Zákonní zástupci', $users, '(Žádné)', FALSE, TRUE)->setRequired();
            $f->addRadioList('gender', 'Pohlaví', [
                Gender::MALE => 'Chlapec',
                Gender::FEMALE => 'Dívka',
            ]);
            $f->addDateTimePicker('dateBorn', 'Datum narození');
            $f->addCheckbox('swimmer', 'Plavec');
            $f->addCheckbox('adhd', 'ADHD');
            $f->addTextArea('health', 'Zdravotní stav');
            //$f->addTextArea('allergy', 'Alergie');
            $f->addTextArea('notes', 'Poznámka');

            $f->addSubmit('save', 'Uložit');
            if($id !== NULL) {
                $f->addSubmit('delete', 'Odstranit')->getControlPrototype()
                    ->attrs['onclick'] = 'if(!confirm("Opravdu smazat?")){return false;}';
            }
            $f->onSuccess[] = function(Form $f) use ($id, $orm) {
                if($f->isSubmitted() === $f['save']) {
                    $data = $f->getValues(TRUE);
                    $parents = Arrays::pick($data, 'parents');
                    if($id === NULL) {
                        try {
                            $child = Child::createFromArray($orm->users->get($parents[0]), $data);
                        } catch (FlashMessageException $e) {
                            $this->presenter->flashMessage($e->getFlashMessage());
                            return;
                        }
                        $child->parents->set($parents);
                        $orm->persistAndFlush($child);
                        $this->presenter->flashMessage('Uloženo.', 'success');
                    } else {
                        /** @var Child $child */
                        $child = $orm->children->get($id);
                        try {
                            $child->updateInfo(
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
                        $child->parents->set($parents);
                        $orm->persistAndFlush($child);
                        $this->presenter->flashMessage('Uloženo.', 'success');
                    }
                    $this->presenter->redirect('children');
                } else if($id !== NULL && $f->isSubmitted() === $f['delete']) {
                    $orm->remove($orm->children->get($id));
                    $orm->flush();
                    $this->presenter->flashMessage('Dítě bylo smazáno.', 'success');
                    $this->presenter->redirect('children');
                }
            };
            if($id !== NULL) {
                /** @var Child $child */
                $child = $orm->children->get($id);
                $f->setValues($child->getValues());
                $f['parents']->setValue($child->parents->get()->fetchPairs(NULL, 'id'));
            }
            $this->addComponent($f, 'form');
        };
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->render();
    }

}
