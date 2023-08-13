<?php

namespace VCD\UI\FrontModule\UserModule;

use Hafo\Facebook\FacebookAvatars;
use Hafo\NetteBridge\Forms\FormFactory;
use Hafo\Security\Storage\Avatars;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Context;
use Nette\Http\FileUpload;
use Nette\Utils\Html;
use Nette\Utils\Image;
use Nextras\Orm\Collection\ICollection;
use VCD\UI\FrontModule\UserModule\AddPhotoControl;
use VCD\Users\Newsletter;
use VCD2\Users\Consent;
use VCD2\Users\Service\Consents;

class ConsentsPresenter extends BasePresenter {

    const LINK_DEFAULT = ':Front:User:Consents:default';

    function actionDefault() {
        if(!$this->user->isLoggedIn()) {
            throw new BadRequestException;
        }
        
        $user = $this->userContext->getEntity();
        
        $smsConsent = $this->orm->consents->findBy([
            'user' => $user->id,
            'type' => Consent::TYPE_SMS_MARKETING,
        ])->orderBy('consentedAt', ICollection::DESC)->fetch();
        $emailConsent = $this->orm->consents->findBy([
            'user' => $user->id,
            'type' => Consent::EMAIL_TYPES,
        ])->orderBy('consentedAt', ICollection::DESC)->fetch();

        /** @var Form $f */
        $f = $this->container->get(FormFactory::class)->create();
        $f->addCheckbox('smsConsent', Html::el()->setHtml('Souhlasím se zasíláním novinek formou SMS'));
        $f->addCheckbox('emailConsent', Html::el()->setHtml('Souhlasím se zasíláním novinek na e-mail'));
        $f->addSubmit('save', 'Uložit změny');
        $f->onSuccess[] = function(Form $f) use ($user) {
            if($f->isSubmitted() === $f['save']) {

                $data = $f->getValues(TRUE);
                $consents = $this->container->get(Consents::class);
                $newsletter = $this->container->get(Newsletter::class);

                if($data['smsConsent']) {
                    $consents->addConsent(Consent::TYPE_SMS_MARKETING, $f['smsConsent']->caption);
                } else {
                    $consents->cancelConsent(Consent::TYPE_SMS_MARKETING);
                }

                if($data['emailConsent']) {
                    $consents->addConsent(Consent::TYPE_EMAIL_MARKETING, $f['emailConsent']->caption, $user->email);
                    $newsletter->add($user->email);
                } else {
                    $consents->cancelConsent(Consent::EMAIL_TYPES);
                    $newsletter->remove($user->email);
                }

                $this->flashMessage('Změny uloženy.', 'success');
                $this->redirect('this');
            }
        };
        $f->setValues([
            'smsConsent' => $smsConsent !== NULL,
            'emailConsent' => $emailConsent !== NULL,
        ]);
        $this->addComponent($f, 'form');

        $this->template->smsConsent = $smsConsent;
        $this->template->emailConsent = $emailConsent;
        $this->template->content = $this->container->get(Context::class)->table('vcd_page')->where('slug = "souhlasy" AND special = 1')->fetch()['content'];
    }

}
