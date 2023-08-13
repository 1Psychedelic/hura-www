<?php

namespace VCD\UI\FrontModule\WebModule;

use Hafo\DI\Container;
use Hafo\NetteBridge\Forms\FormFactory;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Tomaj\Form\Renderer\BootstrapInlineRenderer;
use Tomaj\Form\Renderer\BootstrapRenderer;
use VCD2\Ebooks\Ebook;
use VCD2\Ebooks\EbookDownload;
use VCD2\Ebooks\EbookDownloadLink;
use VCD2\Ebooks\Service\Ebooks;
use VCD2\Emails\Service\Emails\EbookMail;
use VCD2\Orm;
use VCD2\Users\Consent;
use VCD2\Users\Service\Consents;
use VCD2\Users\Service\UserContext;

/**
 * @method onDownload(EbookDownloadLink $download)
 */
class EbookControl extends Control {

    public $onDownload = [];

    private $ebook;

    private $ebooksDir;
    
    private $orm;

    private $ebooks;

    private $formFactory;

    private $user;

    private $askForEmail;

    function __construct(Container $c, Ebook $ebook) {
        $this->ebook = $ebook;
        $this->ebooksDir = $c->get('ebooks');
        $this->orm = $c->get(Orm::class);
        $this->formFactory = $c->get(FormFactory::class);
        $this->user = $c->get(UserContext::class)->getEntity();
        $this->ebooks = $c->get(Ebooks::class);
        $this->askForEmail =TRUE;// $this->user === NULL || empty($this->user->email);

        $this->onAnchor[] = function() use ($ebook, $c) {

            $f = $this->formFactory->create();
            $f->setRenderer(new BootstrapInlineRenderer());
            $f->addText('email', 'E-mail')->setRequired()->addRule(Form::EMAIL);

            $c->get(Consents::class)
                ->addConsentCheckbox($f, Consent::TYPE_EMAIL_MARKETING,
                    'Souhlasím s dodatečným zpracováním svého e-mailu pro zasílání dalších zajímavých informací dle výše uvedených zásad ochrany osobních údajů.',
                    'agreeEmail',
                    'Pro získání e-booku zdarma musíte souhlasit se zpracováním e-mailu.',
                    'getLink',
                    function($data) {
                        return $data['email'];
                    }
                );
            
            $f->addSubmit('getLink', 'Získat odkaz pro stažení e-booku');
            $f->onSuccess[] = function(Form $f) use ($ebook, $c) {
                if($f->isSubmitted() === $f['getLink']) {
                    $email = $f->getValues(TRUE)['email'];

                    $link = $this->ebooks->createDownloadLink($ebook, $email, $this->user);

                    $c->get(EbookMail::class)->send($email, $link->hash);

                    $this->onDownload($link);

                    $this->presenter->flashMessage('Váš odkaz pro stažení e-booku byl odeslán na zadanou e-mailovou adresu.', 'success');
                    $this->presenter->redirect('this');
                }
            };
            if($this->user !== NULL) {
                $f->setValues([
                    'email' => $this->user->email,
                ]);
            }
            $this->addComponent($f, 'form');
        };
    }

    public function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->ebook = $this->ebook;
        $this->template->askForEmail = $this->askForEmail;
        $this->template->id = $this->getUniqueId();
        $this->template->consentDocumentUrl = Consent::DOCUMENT_URL;
        $this->template->render();
    }

}
