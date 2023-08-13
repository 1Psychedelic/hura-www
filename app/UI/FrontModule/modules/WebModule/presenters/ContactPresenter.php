<?php

namespace VCD\UI\FrontModule\WebModule;

use Hafo\Google\ReCaptcha\ReCaptchaV3;
use Hafo\NetteBridge\Forms\FormFactory;
use Nette\Application\UI\Form;
use Nette\Database\Context;
use Nette\Utils\Html;
use Tomaj\Form\Renderer\BootstrapRenderer;
use VCD2\Emails\Service\Emails\ContactFormMail;
use VCD2\Users\Consent;
use VCD2\Users\Service\Consents;

class ContactPresenter extends BasePresenter {

    const LINK_DEFAULT = ':Front:Web:Contact:default';

    function actionDefault() {
        $db = $this->container->get(Context::class);
        $recaptcha = $this->container->get(ReCaptchaV3::class);

        $this->template->recaptchaInitLibrary = Html::el()->setHtml($recaptcha->getInitLibraryHtml());
        $this->template->recaptchaInitForm = Html::el()->setHtml($recaptcha->getInitFormHtml('contactForm', 'recaptcha'));

        $user = $this->userContext->getEntity();

        /** @var Form $f */
        $f = $this->container->get(FormFactory::class)->create();
        $f->setRenderer(new BootstrapRenderer);
        $f->addText('email', 'Váš e-mail')->setRequired()->addRule(Form::EMAIL);
        $f->addText('name', 'Vaše jméno')->setRequired();
        $f->addTextArea('message', 'Zpráva', NULL, 10)->setRequired();
        $f->addHidden('recaptcha')->getControlPrototype()->addClass('recaptcha');

        $this->container->get(Consents::class)
            ->addConsentCheckbox($f, Consent::TYPE_CONTACT_FORM,
                Html::el()->setHtml('Přečetl/a jsem si <a href="' . Consent::DOCUMENT_URL . '" target="_blank">Zásady ochrany osobních údajů</a> a souhlasím s jejich zpracováním dle uvedených zásad.'),
                NULL,
                'Pro odeslání formuláře musíte souhlasit se zpracováním osobních údajů.',
                'send',
                function($data) {
                    return $data['email'];
                }
            );

        $f->addSubmit('send', 'Odeslat zprávu');
        if($user !== NULL) {
            $f->setValues([
                'email' => $user->email,
                'name' => $user->name,
            ]);
        }
        $f->onValidate[] = function(Form $f) use ($recaptcha) {
            $data = $f->getValues(TRUE);
            $verified = $recaptcha->verify($data['recaptcha']);
            if(!$verified) {
                $f->addError('Vaše zpráva nebyla odeslána - Google vyhodnotil, že nejste člověk. Pošlete nám prosím e-mail klasickým způsobem nebo nás kontaktujte telefonicky. Omlouváme se za komplikace.');
            }
        };
        $f->onSuccess[] = function(Form $f) {
            if($f->isSubmitted() === $f['send']) {
                $data = $f->getValues(TRUE);
                $this->container->get(ContactFormMail::class)->send($data['name'], $data['email'], $data['message']);
                $this->flashMessage('Zpráva byla úspěšně odeslána.', 'success');
            }
            $this->redirect('this');
        };
        $this->addComponent($f, 'form');
        $this->template->titlePrefix = 'Kontakty';
        $this->template->content = $db->table('vcd_page')->where('slug = "kontakty" AND special = 1')->fetch()['content'];
    }

}
