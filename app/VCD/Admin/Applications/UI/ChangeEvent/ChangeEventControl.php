<?php
declare(strict_types=1);

namespace VCD\Admin\Applications\UI;

use Hafo\DI\Container;
use Hafo\Utils\Arrays;
use Monolog\Logger;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use VCD2\Applications\StepChoice;
use VCD2\Orm;
use VCD2\UI\Admin\Forms\AdminFormRenderer;

class ChangeEventControl extends Control
{
    private $container;

    /** @var Orm */
    private $orm;

    /** @var Logger */
    private $logger;

    private $view = 'default';

    public function __construct(Container $container, int $applicationId, ?int $eventId)
    {
        $this->container = $container;
        $this->orm = $container->get(Orm::class);
        $this->logger = $container->get(Logger::class)->withName(get_class($this));

        $this->onAnchor[] = function () use ($applicationId, $eventId) {
            if ($eventId === null) {
                $this->addComponent($this->createEventForm($applicationId), 'form');
                $this->view = 'default';
            } else {
                $this->addComponent($this->createDetailsForm($applicationId, $eventId), 'form');
                $this->view = 'event';
            }
        };
    }

    private function createDetailsForm(int $applicationId, int $eventId): Form
    {
        $application = $this->orm->applications->get($applicationId);
        $this->template->application = $application;

        $event = $this->orm->events->get($eventId);
        $this->template->event = $event;

        $f = new Form;

        $f->setRenderer(new AdminFormRenderer());

        foreach ($event->steps as $step) {
            $options = [];
            foreach ($step->options as $option) {
                $options[$option->id] = $option->option;
            }
            $f->addRadioList('step_' . $step->id, $step->prompt, $options);
        }

        $f->addSubmit('save', 'Uložit změny');

        $f->onSuccess[] = function (Form $f) use ($application, $event) {
            if ($f->isSubmitted() === $f['save']) {
                $data = $f->getValues(true);

                $oldEvent = $application->event->id;

                // set new event
                $application->event = $event;

                $steps = Arrays::subArrayByPrefix($data, 'step_', true, true);

                // remove invalid steps
                foreach ($application->stepChoices as $stepChoice) {
                    $this->orm->remove($stepChoice);
                }

                // save selected steps
                foreach ($steps as $step => $option) {
                    $stepChoice = $this->orm->applicationStepChoices->getBy(['application' => $application, 'step' => $step]);
                    if ($stepChoice === null) {
                        $stepChoice = new StepChoice($application, $this->orm->eventStepOptions->get($option));
                    }
                    $this->orm->persist($stepChoice);
                }

                $oldPrice = $application->price;

                $application->refreshDiscount();
                $application->recalculatePrice();

                $newPrice = $application->price;

                $this->orm->persistAndFlush($application);

                $this->logger->info(sprintf(
                    'Změněna akce z %s na %s u přihlášky %s - cena změněna z %s na %s',
                    $oldEvent,
                    $event->id,
                    $application->id,
                    $oldPrice,
                    $newPrice
                ));

                $message = sprintf('Akce byla nastavena, cena se změnila z %sKč na %sKč.', $oldPrice, $newPrice);
                $this->presenter->flashMessage($message, 'success');
                $this->presenter->redirect('gotoApplications!');
            }
        };

        return $f;
    }

    private function createEventForm(int $applicationId): Form
    {
        $application = $this->orm->applications->get($applicationId);

        $options = $this->orm->events->findSelectOptionsForAdmin();

        $f = new Form;
        $f->setRenderer(new AdminFormRenderer());
        $f->addSelect('event', 'Akce', $options)
            ->getControlPrototype()->addClass('select2');
        $f->addSubmit('continue', 'Pokračovat');

        $f->onSuccess[] = function (Form $f) {
            if ($f->isSubmitted() === $f['continue']) {
                $this->presenter->redirect('this', ['eventId' => $f->getValues(true)['event']]);
            }
        };

        $f->setValues(['event' => $application->event->id]);

        return $f;
    }

    public function render()
    {
        $this->template->setFile(__DIR__ . '/' . $this->view . '.latte');
        $this->template->render();
    }
}
