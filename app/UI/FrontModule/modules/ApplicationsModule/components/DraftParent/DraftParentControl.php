<?php

namespace VCD\UI\FrontModule\ApplicationsModule;

use Hafo\Ares\Ares;
use Hafo\DI\Container;
use Hafo\NetteBridge\Forms\FormFactory;
use Hafo\Security\Authentication\IdAuthenticator;
use Hafo\Security\SecurityException;
use Hafo\Security\Storage\Emails;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Utils\Arrays;
use Nette\Utils\Html;
use VCD\Users\Newsletter;
use VCD2\Applications\Application;
use VCD2\Applications\Service\Drafts;
use VCD2\Emails\Service\Emails\AccountCreatedMail;
use VCD2\Emails\Service\Emails\ApplicationVerifyMail;
use VCD2\Orm;
use VCD2\Users\Consent;
use VCD2\Users\Service\AutomaticSignup;
use VCD2\Users\Service\Consents;
use VCD2\Users\Service\UserContext;
use VCD2\Users\User;

/**
 * @method onSave()
 */
class DraftParentControl extends Control {

    public $onSave = [];

    /** @var Container */
    private $container;

    /** @var Orm */
    private $orm;

    /** @var User|NULL */
    private $user;

    /** @var Application */
    private $draft;

    function __construct(Container $container, Application $draft) {

        $this->onAnchor[] = function() use ($container, $draft) {

            $this->container = $container;
            $this->draft = $draft;
            $this->orm = $orm = $container->get(Orm::class);
            $formFactory = $container->get(FormFactory::class);
            $newsletter = $container->get(Newsletter::class);
            $this->user = $user = $container->get(UserContext::class)->getEntity();
            $drafts = $container->get(Drafts::class);
            $consents = $container->get(Consents::class);

            /** @var Consent[] $addedConsents */
            $addedConsents = [];

            $consents->onConsentAdded[] = function(Consent $consent) use (&$addedConsents) {
                $addedConsents[] = $consent;
            };

            /** @var Form $f */
            $f = $formFactory->create();

            $f->addText('name', 'Jméno a příjmení zákonného zástupce')->setRequired()->addRule(Form::PATTERN, 'Zadejte prosím své jméno a příjmení', '(.*)\s(.*)');

            if($user === NULL || !$user->emailVerified) {
                $f->addText('email', 'E-mail')->setRequired()->addRule(Form::EMAIL);
            }
            if($user === NULL || !$user->phoneVerified) {
                $f->addText('phone', 'Telefon')->setRequired(); // todo pravidla
            }
            $f->addText('city', 'Město')->setRequired();
            $f->addText('street', 'Ulice a číslo domu')->setRequired();
            $f->addText('zip', 'PSČ')->setRequired();

            if($this->draft->canBePaidOnInvoice) {
                $f->addCheckbox('isPayingOnInvoice', 'Akci hradí zaměstnavatel, přeji si vystavit pro něj fakturu')
                    ->addCondition(Form::EQUAL, TRUE)
                        ->toggle('invoiceData');
                $f->addText('invoiceName', 'Název společnosti')->setNullable();
                $f->addText('invoiceIco', 'IČO')->setNullable();
                $f->addText('invoiceDic', 'DIČ')->setNullable();
                $f->addText('invoiceCity', 'Sídlo - město')->setNullable();
                $f->addText('invoiceStreet', 'Sídlo - ulice a č.p.')->setNullable();
                $f->addText('invoiceZip', 'Sídlo - PSČ')->setNullable();

                foreach (['invoiceName', 'invoiceIco', 'invoiceCity', 'invoiceStreet', 'invoiceZip'] as $field) {
                    $f[$field]->addConditionOn($f['isPayingOnInvoice'], Form::EQUAL, TRUE)
                        ->setRequired();
                }
            }

            $terms = 'https://volnycasdeti.cz/www/page/smluvni-podminky/VOP.pdf';
            $guideline = 'https://volnycasdeti.cz/www//page/smluvni-podminky/Jak-to-u-nas-chodi.pdf';
            $gdpr = Consent::DOCUMENT_URL;

            $consents->addConsentCheckbox($f, Consent::TYPE_PERSONAL_INFORMATION,
                Html::el()->setHtml('Přečetl/a jsem si <a href="' . $gdpr . '" target="_blank">Zásady ochrany osobních údajů</a> a souhlasím s nimi.'),
                'agreedPersonalData',
                'Před pokračováním musíte souhlasit se zásadami ochrany osobních údajů.',
                'send'
            );

            $consents->addConsentCheckbox($f, Consent::TYPE_TERMS_AND_CONDITIONS,
                Html::el()->setHtml('Přečetl/a jsem si <a href="' . $terms . '" target="_blank">Všeobecné obchodní podmínky</a> a s nimi související dokument <a href="' . $guideline . '" target="_blank">"Jak to u nás chodí"</a> a souhlasím s nimi.'),
                'agreedTermsAndConditions',
                'Před pokračováním musíte souhlasit se všeobecnými obchodními podmínkami a s dokumentem "Jak to u nás chodí".',
                'send'
            );

            /*$consents->addConsentCheckbox($f, Consent::TYPE_PARENT_GUIDELINE,
                Html::el()->setHtml('Seznámil/a jsem se s dokumentem <a href="' . $guideline . '" target="_blank">"Jak to u nás chodí"</a> a souhlasím s ním.'),
                'agreedParentGuideline',
                'Před pokračováním musíte souhlasit s dokumentem "Jak to u nás chodí".',
                'send'
            );*/

            /*$consents->addConsentCheckbox($f, Consent::TYPE_PERSONAL_INFORMATION,
                'Poskytnutím svých osobních údajů obsažených v přihlašovacím formuláři dávám v souladu s ustanovením zákona č. 101/2000 Sb o ochraně osobních údajů souhlas k jejich zpracování a uchování ve Vaší databázi.',
                'agreedPersonalData',
                'Před pokračováním musíte souhlasit se zpracováním osobních údajů.',
                'send'
            );*/

            $consents->addConsentCheckbox($f, Consent::TYPE_PHOTOGRAPHY,
                'Svým podpisem dávám souhlas k pořizování snímků a videí z výletu a jejich následovnému použití k propagačním účelům pro další pořádané události.',
                'agreedPhotography',
                'Před pokračováním musíte souhlasit s pořizováním snímků.',
                'send'
            );

            $consents->addConsentCheckbox($f, Consent::TYPE_SMS_MARKETING,
                'Přeji si dostávat informace o následujících pobytových akcích formou SMS.',
                'agreedSms',
                FALSE,
                'send'
            );

            if($user === NULL || !$newsletter->isAdded($user->email)) {
                $consents->addConsentCheckbox($f, Consent::TYPE_EMAIL_MARKETING,
                    'Přeji si dostávat novinky na e-mail.',
                    'newsletter',
                    FALSE,
                    'send',
                    function($data) use ($user) {
                        /** @var User|NULL $user */
                        return $user === NULL || !$user->emailVerified ? $data['email'] : $user->email;
                    }
                );
            }

            $f->addButtonSubmit('send', Html::el()->addHtml('Uložit a pokračovat ')->addHtml(Html::el('span')->class('glyphicon glyphicon-arrow-right')));

            $invoiceFields = [
                'isPayingOnInvoice',
                'invoiceName',
                'invoiceIco',
                'invoiceDic',
                'invoiceStreet',
                'invoiceCity',
                'invoiceZip',
            ];
            $fields = array_merge([
                'name',
                'email',
                'phone',
                'city',
                'street',
                'zip',
                'saveProfile',
                'agreedPersonalData',
                'agreedPhotography',
                'agreedTermsAndConditions',
                'agreedSms',
            ], $this->draft->canBePaidOnInvoice ? $invoiceFields : []);
            $f->setValues($user === NULL ? $draft->getValues($fields) : array_merge(array_filter($user->getValues($fields)), array_filter($draft->getValues($fields))));
            $f->setValues([
                'agreedGdpr' => $consents->hasValidConsent(Consent::TYPE_PERSONAL_INFORMATION),
                //'agreedTermsAndConditions' => $consents->hasValidConsent(Consent::TYPE_TERMS_AND_CONDITIONS),
                //'agreedParentGuideline' => $consents->hasValidConsent(Consent::TYPE_PARENT_GUIDELINE),
            ]);
            $f->onSuccess[] = function(Form $f) use ($draft, $user, $newsletter, $orm, $drafts, $addedConsents) {
                if($f->isSubmitted() === $f['send']) {
                    $data = $f->getValues(TRUE);

                    // hack
                    if($user !== NULL && $user->emailVerified) {
                        $data['email'] = $user->email;
                    }
                    if($user !== NULL && $user->phoneVerified) {
                        $data['phone'] = $user->phone;
                    }

                    // kontrola e-mailu
                    if(!$this->checkEmail($data['email'])) {
                        return;
                    }

                    // přidání do odběru
                    if(Arrays::pick($data, 'newsletter', FALSE)) {
                        $newsletter->add($data['email']);
                    }

                    // upravit profil
                    if($user !== NULL) {
                        $user->updateInfo($data['name'], $data['phone'], $data['city'], $data['street'], $data['zip']);
                        $orm->persist($user);
                    }

                    // upravit údaje
                    $draft->updateParentInfo($data['name'], $data['email'], $data['phone'], $data['city'], $data['street'], $data['zip']);
                    $draft->markAgreement($data['agreedPersonalData'], $data['agreedTermsAndConditions'], $data['agreedPhotography']);

                    // úhrada zaměstnavatelem
                    if($this->draft->canBePaidOnInvoice && $data['isPayingOnInvoice']) {
                        $draft->setPayingOnInvoice(
                            $data['invoiceName'],
                            $data['invoiceIco'],
                            $data['invoiceDic'],
                            $data['invoiceCity'],
                            $data['invoiceStreet'],
                            $data['invoiceZip'],
                            null
                        );
                    } else {
                        $draft->resetPayingOnInvoice();
                    }

                    // uložit
                    $drafts->saveDraft($draft);

                    // založení účtu + přihlášení
                    if($user === NULL) {
                        $autoSignup = $this->container->get(AutomaticSignup::class);
                        $autoSignup->onSignup[] = function(User $u) use (&$user, $draft) {
                            $user = $u;
                            $draft->user = $u;
                            $this->container->get(IdAuthenticator::class)->login($u->id);
                            $this->container->get(AccountCreatedMail::class)->send($user->email);
                        };
                        $autoSignup->createAccount($data['email']);
                    }

                    // uložit úhradu zaměstnavatelem do profilu
                    if($this->draft->canBePaidOnInvoice) {
                        if($data['isPayingOnInvoice']) {
                            $user->setPayingOnInvoice(
                                $data['invoiceName'],
                                $data['invoiceIco'],
                                $data['invoiceDic'],
                                $data['invoiceCity'],
                                $data['invoiceStreet'],
                                $data['invoiceZip'],
                                null
                            );
                        } else {
                            $user->resetPayingOnInvoice();
                        }
                    }

                    // souhlasy
                    $user->markAgreement($data['agreedPersonalData'], $data['agreedTermsAndConditions'], $data['agreedPhotography'], $data['agreedSms']);
                    foreach($addedConsents as $addedConsent) {
                        $addedConsent->user = $user;
                        $orm->persist($addedConsent);
                    }
                    $orm->persist($user);
                    $orm->flush();

                    $this->onSave();
                }
            };
            $this->addComponent($f, 'form');
        };
    }

    function handleLoadSubject($ico) {
        $subject = $this->container->get(Ares::class)->getSubjectByIco($ico);
        if ($subject === NULL) {
            $this->presenter->sendPayload();
        }

        $prefix = 'frm-' . $this->getUniqueId() . '-form-invoice';
        $this->getPresenter()->payload->formReplace = (object) [
            $prefix . 'Name' => $subject->getName(),
            $prefix . 'Ico' => $subject->getIco(),
            $prefix . 'Dic' => $subject->getDic(),
            $prefix . 'Street' => $subject->getStreet(),
            $prefix . 'City' => $subject->getCity(),
            $prefix . 'Zip' => $subject->getZip(),
        ];
        $this->presenter->sendPayload();
    }

    function handleCheckEmail($email) {
        if($this->user === NULL || $this->user->email !== $email) {
            $existingUser = $this->orm->users->getByEmail($email);
            if($existingUser !== NULL) {
                if($existingUser->canLogin) {
                    $this->template->emailErrorMessage = Html::el()->setHtml('Tento e-mail je již registrován. Pro pokračování pod tímto e-mailem se prosím přihlašte.');
                } else {
                    $this->container->get(AccountCreatedMail::class)->send($email);
                    $this->template->emailErrorMessage = 'Tento e-mail je již registrován, ale účet není aktivovaný. Instrukce pro aktivaci účtu najdete ve své e-mailové schránce. V případě potíží nás neváhejte kontaktovat.';
                }
            } else {
                try {
                    $this->container->get(AutomaticSignup::class)->check($email);
                } catch (SecurityException $e) {
                    $this->container->get(AutomaticSignup::class)->createAccount($email);
                    $hash = $this->container->get(Emails::class)->requestEmailVerifyHash($email);
                    $this->container->get(ApplicationVerifyMail::class)->send($email, $hash, $this->draft->event->id);
                    $this->template->emailErrorMessage = 'Tento e-mail je již registrován, ale účet není aktivovaný. Instrukce pro aktivaci účtu najdete ve své e-mailové schránce. V případě potíží nás neváhejte kontaktovat.';
                }
            }
        }
        $this->redrawControl('emailError');
        $this->redrawControl('emailError2');
    }

    protected function checkEmail($email) {
        if($this->user === NULL || $this->user->email !== $email) {
            $existingUser = $this->orm->users->getByEmail($email);
            if($existingUser !== NULL) {
                if($existingUser->canLogin) {
                    $this->presenter->flashMessage(Html::el()->setHtml('Zadaný e-mail je již registrován. Pro pokračování pod tímto e-mailem se prosím <a href="#" class="vcd-login-button">přihlašte</a>.'), 'danger');
                    return FALSE;
                } else {
                    $this->container->get(AccountCreatedMail::class)->send($email);
                    $this->presenter->flashMessage('Tento e-mail je již registrován, ale účet není aktivovaný. Instrukce pro aktivaci účtu najdete ve své e-mailové schránce. V případě potíží nás neváhejte kontaktovat.', 'danger');
                    return FALSE;
                }
            } else {
                try {
                    $this->container->get(AutomaticSignup::class)->check($email);
                    return TRUE;
                } catch (SecurityException $e) {
                    $this->container->get(AutomaticSignup::class)->createAccount($email);
                    $hash = $this->container->get(Emails::class)->requestEmailVerifyHash($email);
                    $this->container->get(ApplicationVerifyMail::class)->send($email, $hash, $this->draft->event->id);
                    $this->template->emailErrorMessage = 'Tento e-mail je již registrován, ale účet není aktivovaný. Instrukce pro aktivaci účtu najdete ve své e-mailové schránce. V případě potíží nás neváhejte kontaktovat.';
                    return FALSE;
                }
            }
        }
        return TRUE;
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->userEntity = $this->user;
        $this->template->draft = $this->draft;
        $this->template->render();
    }

}
