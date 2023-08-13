<?php

namespace VCD\Admin\Users\UI;

use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Database\Context;
use Nette\Utils\Arrays;
use Nette\Utils\Html;
use Tomaj\Form\Renderer\BootstrapRenderer;
use VCD2\Applications\Application;
use VCD2\Orm;
use VCD2\Users\InsufficientCreditException;
use VCD2\Users\Service\Credits;
use VCD2\Users\User;

class CreditsGiveControl extends Control {

    const PARTICIPANTS = 0;
    const SPECIFIC_USERS = 1;

    private $container;

    function __construct(ContainerInterface $container) {
        $this->container = $container;
        
        $this->onAnchor[] = function() {

            $orm = $this->container->get(Orm::class);

            $users = $orm->users->findSelectOptions();
            $events = $orm->events->findSelectOptionsForAdmin();

            $f = new Form;
            $f->setRenderer(new BootstrapRenderer);
            $f->addProtection();

            $f->addRadioList('method', 'Přidat kredity', [
                self::PARTICIPANTS => Html::el()->addHtml('účastníkům akce<br><small class="text-muted">Kredity budou vynásobeny počtem účastníků.</small>'),
                self::SPECIFIC_USERS => 'konkrétním uživatelům'
            ]);

            $f->addXMultiSelect('users', 'Uživatelé', $users, '(Není vybráno)', FALSE, TRUE)
                ->setOption('id', 'users');

            $f->addXSelect('event', 'Událost', $events)
                ->setOption('id', 'event');

            $f->addText('amount', 'Množství');
            $f->addDateTimePicker('expiresAt', 'Platnost do')
                ->setNullable()
                ->setOption('id', 'expiresAt');
            $f->addTextArea('notes', 'Interní poznámka')
                ->setNullable();

            $f->addSubmit('give', 'Přidat kredity');

            $f['method']->addCondition(Form::EQUAL, self::PARTICIPANTS)
                ->toggle('event');
            $f['method']->addCondition(Form::EQUAL, self::SPECIFIC_USERS)
                ->toggle('users');

            $f['amount']->addCondition(Form::MIN, 0)
                ->toggle('expiresAt');

            $f->onSuccess[] = function(Form $f) use ($orm) {
                if($f->isSubmitted() === $f['give']) {
                    $data = $f->getValues(TRUE);

                    $expiration = Arrays::get($data, 'expiresAt', NULL);
                    $expiration = $expiration === NULL ? NULL : new \DateTimeImmutable($expiration);

                    $credits = $this->container->get(Credits::class);
                    $ok = 0;
                    $safeMoveCredits = function($user, $amount, $notes = NULL, $application = NULL) use ($credits, $orm, $expiration, &$ok) {
                        try {
                            if($amount > 0) {
                                $credits->add($user, $amount, $expiration, $notes);
                            } else {
                                $credits->spend($user, $amount, $notes, $application);
                            }
                            $ok++;
                        } catch (InsufficientCreditException $e) {
                            /** @var User $user */
                            $userEntity = $orm->users->get($user);
                            $this->presenter->flashMessage(sprintf('Uživateli %s nebyly odečteny kredity, protože by se dostal do mínusu.', $userEntity), 'warning');
                        }
                    };
                    if($data['method'] === self::PARTICIPANTS) {

                        $users = $orm->applications->findApplicationUserPairs($data['event']);
                        foreach($users as $application => $user) {
                            /** @var Application $application */
                            $application = $orm->applications->get($application);
                            $children = $application->children->countStored();
                            $safeMoveCredits($user, $data['amount'] * $children, $data['notes'], $application->id);
                        }

                    } else if($data['method'] === self::SPECIFIC_USERS) {

                        foreach($data['users'] as $user) {
                            $safeMoveCredits($user, $data['amount'], $data['notes']);
                        }

                    }

                    if($ok > 0) {
                        $this->presenter->flashMessage(sprintf('Kredity %s.', $data['amount'] < 0 ? 'odebrány' : 'rozdány'), 'success');
                    }
                    $this->presenter->redirect('credits');
                }
            };

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
