<?php

namespace VCD\UI\FrontModule\EventsModule;

use Hafo\NetteBridge\Forms\FormFactory;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Utils\Html;
use Tomaj\Form\Renderer\BootstrapRenderer;
use VCD\Users\Newsletter;
use VCD2\Events\Event;
use VCD2\Users\Consent;
use VCD2\Users\Service\Consents;

class EventsPresenter extends BasePresenter
{
    const LINK_DEFAULT = ':Front:Events:Events:default';

    public function actionDefault($type)
    {
        if (!in_array($type, Event::TYPES_AVAILABLE)) {
            throw new BadRequestException;
        }

        $events = $this->orm->events->findUpcoming($type, $this->user->isInRole('admin'))->orderBy('starts');
        $countEvents = $events->count();

        if ($countEvents === 1) {
            $this->redirect(302, EventPresenter::LINK_DEFAULT, ['_event' => $events->fetch()->slug]);
        } elseif ($countEvents === 0) {
            $this->template->empty = true;
            $this->setupNewsletterForm();
        } else {
            $this->template->empty = false;
        }

        $this->addComponent(new EventsControl($events), 'events');

        if ($type === Event::TYPE_CAMP) {
            $this->template->titlePrefix = 'Letní tábory 2020';
        } else {
            $this->template->titlePrefix = 'Víkendové výlety';
        }
        //$this->template->titlePrefix = Events\DefaultModel\EventItem::$names[$type] . 'y';
        $this->template->type = $type;
        $this->template->typeCamp = Event::TYPE_CAMP;
        $this->template->typeTrip = Event::TYPE_TRIP;
    }

    public function actionCamps()
    {
        $camps = $this->orm->events->findUpcoming(Event::TYPE_CAMP, $this->user->isInRole('admin'))->count();
        $springCamps = $this->orm->events->findUpcoming(Event::TYPE_CAMP_SPRING, $this->user->isInRole('admin'))->count();

        if ($springCamps === 0) {
            $this->redirect(self::LINK_DEFAULT, ['type' => Event::TYPE_CAMP]);
        } elseif ($springCamps > 0 && $camps === 0) {
            $this->redirect(self::LINK_DEFAULT, ['type' => Event::TYPE_CAMP_SPRING]);
        }

        $this->template->eventsLink = self::LINK_DEFAULT;
        $this->template->typeCamp = Event::TYPE_CAMP;
        $this->template->typeCampSpring = Event::TYPE_CAMP_SPRING;
    }

    private function setupNewsletterForm()
    {
        if ($this->user->isLoggedIn() && $this->container->get(Newsletter::class)->isAdded($this->user->identity->data['email'])) {
            $this->template->showNewsletter = false;
        } else {
            $this->template->showNewsletter = true;
            /** @var Form $f */
            $f = $this->container->get(FormFactory::class)->create();
            $f->setRenderer(new BootstrapRenderer);
            $f->addEmail('email', 'E-mail');

            $this->container->get(Consents::class)
                ->addConsentCheckbox($f, Consent::TYPE_EMAIL_MARKETING,
                    Html::el()->setHtml('Souhlasím se <a href="' . Consent::DOCUMENT_URL . '" target="_blank">Zásadami ochrany osobních údajů</a> a přeji si dostávat novinky na e-mail.'),
                    null,
                    true,
                    'subscribe',
                    function ($data) {
                        return $data['email'];
                    }
                );

            $f->addSubmit('subscribe', 'Přihlásit se k odběru novinek');
            if ($this->user->isLoggedIn()) {
                $f['email']->setValue($this->user->identity->data['email']);
            }
            $f->onSuccess[] = function (Form $f) {
                if ($f->isSubmitted() === $f['subscribe']) {
                    $data = $f->getValues(true);
                    $this->container->get(Newsletter::class)->add($data['email']);
                    $this->presenter->flashMessage('Vaše e-mailová adresa byla přidána do odběru novinek.', 'success');
                    $this->presenter->redirect('this');
                }
            };
            $this->addComponent($f, 'form');
        }
    }
}
