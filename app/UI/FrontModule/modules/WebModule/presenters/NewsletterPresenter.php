<?php

namespace VCD\UI\FrontModule\WebModule;

use Hafo\Google\ConversionTracking\Tracker;
use Hafo\NetteBridge\Forms\FormFactory;
use Nette\Application\UI\Form;
use Nette\Database\Context;
use Nette\Utils\Html;
use Tomaj\Form\Renderer\BootstrapRenderer;
use VCD\UI\FrontModule\HomepageModule\HomepagePresenter;
use VCD\Users\Newsletter;
use VCD2\Users\Consent;
use VCD2\Users\Service\Consents;

class NewsletterPresenter extends BasePresenter {

    function actionDefault() {
        if($this->user->isLoggedIn() && $this->container->get(Newsletter::class)->isAdded($this->user->identity->data['email'])) {
            $this->redirect(HomepagePresenter::LINK_DEFAULT);
        }
        $this->template->titlePrefix = 'Odebírat novinky e-mailem';
        $this->template->content = $this->container->get(Context::class)->table('vcd_page')->where('slug = "newsletter" AND special = 1')->fetch()['content'];
        /** @var Form $f */
        $f = $this->container->get(FormFactory::class)->create();
        $f->setRenderer(new BootstrapRenderer);
        $f->addEmail('email', 'E-mail');

        $this->container->get(Consents::class)
            ->addConsentCheckbox($f, Consent::TYPE_EMAIL_MARKETING,
                Html::el()->setHtml('Souhlasím se <a href="' . Consent::DOCUMENT_URL . '" target="_blank">Zásadami ochrany osobních údajů</a> a přeji si dostávat novinky na e-mail.'),
                NULL,
                TRUE,
                'subscribe',
                function($data) {
                    return $data['email'];
                }
            );

        $f->addSubmit('subscribe', 'Přihlásit se k odběru novinek');
        if($this->user->isLoggedIn()) {
            $f['email']->setValue($this->user->identity->data['email']);
        }
        $f->onSuccess[] = function(Form $f) {
            if($f->isSubmitted() === $f['subscribe']) {
                $data = $f->getValues(TRUE);
                $this->container->get(Newsletter::class)->add($data['email']);
                $this->container->get(Tracker::class)->addConversion('Stažení ebooku', '4qVJCJ-N1IMBEMiF9YkD');
                $this->presenter->flashMessage('Vaše e-mailová adresa byla přidána do odběru novinek.', 'success');
                $this->presenter->redirect(HomepagePresenter::LINK_DEFAULT);
            }
        };
        $this->addComponent($f, 'form');
    }

}
