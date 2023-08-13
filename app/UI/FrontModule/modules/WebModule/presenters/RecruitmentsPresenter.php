<?php

namespace VCD\UI\FrontModule\WebModule;

use Hafo\Google\ConversionTracking\Tracker;
use Hafo\NetteBridge\Forms\FormFactory;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Context;
use Nette\Http\FileUpload;
use Nette\Mail\IMailer;
use Nette\Mail\Message;
use Nette\Utils\Html;
use Tomaj\Form\Renderer\BootstrapRenderer;
use VCD2\Users\Consent;
use VCD2\Users\Service\Consents;

class RecruitmentsPresenter extends BasePresenter {

    const LINK_DEFAULT = ':Front:Web:Recruitments:default';

    function actionDefault($id = NULL, $apply = FALSE) {
        $db = $this->container->get(Context::class);
        $this->template->titlePrefix = 'Přidej se k nám';
        if($id === NULL) {
            $selection = $db->table('vcd_recruitment')->order('position ASC');
            if(!$this->user->isInRole('admin')) {
                $selection->where('visible = 1');
            }
            $this->template->list = $selection;
            $this->template->content = $db->table('vcd_page')->where('slug = ? AND special = 1', 'pridej-se-k-nam')->fetch()['content'];
        } else {

            $selection = $db->table('vcd_recruitment')->where('slug', $id);
            if(!$this->user->isInRole('admin')) {
                $selection->where('visible = 1');
            }
            $row = $selection->fetch();
            if(!$row) {
                throw new BadRequestException;
            }

            if($apply) {
                $mimes = [
                    //pdf
                    'application/pdf',
                    'application/x-pdf',

                    //docx
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',

                    //odt
                    'application/vnd.oasis.opendocument.text'
                ];
                $statuses = [
                    'student' => 'Jsem student',
                    'working' => 'Pracuji',
                    'unemployed' => 'Jsem nezaměstnaný(á)',
                ];
                $frequencies = [
                    'regularly' => 'Pravidelně',
                    'sometimes' => 'Nárazově'
                ];

                /** @var Form $f */
                $f = $this->container->get(FormFactory::class)->create();
                $f->setRenderer(new BootstrapRenderer);
                $f->addText('name', 'Jméno a příjmení')->setRequired()->addRule(Form::PATTERN, 'Zadejte prosím celé jméno a příjmení', '(.*)\s(.*)');
                $f->addText('age', 'Věk')->setRequired()->addRule(Form::NUMERIC);
                $f->addText('home', 'Bydliště')->setRequired();
                $f->addEmail('email', 'E-mailová adresa')->setRequired();
                $f->addText('phone', 'Mobil')->setRequired();
                $f->addRadioList('status', 'Jsi student, nebo pracuješ?', $statuses)->setRequired();
                $f->addRadioList('frequency', 'Máš zájem účastnit se akcí pravidelně, nebo spíš nárazově?', $frequencies)->setRequired();
                $f->addTextArea('experience', 'Napiš nám své zkušenosti s prací s dětmi' . ($id === 'kucharka' ? ' / s vařením pro skupiny' : ''), NULL, 8)->setRequired();
                $f->addTextArea('motivation', 'Proč máš zájem o tuto pozici a co od ní očekáváš?', NULL, 8)->setRequired();

                $this->container->get(Consents::class)
                    ->addConsentCheckbox($f, Consent::TYPE_RECRUITMENT,
                        Html::el()->setHtml('Přečetl/a jsem si <a href="https://volnycasdeti.cz/www/Dokumenty/GDPR-Zamestnanci-web.pdf" target="_blank">Zásady ochrany osobních údajů</a> a souhlasím s jejich zpracováním dle uvedených zásad.'),
                        NULL,
                        'Pro odeslání formuláře musíte souhlasit se zpracováním osobních údajů.',
                        'send',
                        function($data) {
                            return $data['email'];
                        }
                    );

                //$f->addUpload('cv', 'Životopis - nepovinné (pdf, docx, odt)')
                //    ->addCondition(Form::FILLED)
                //    ->addRule(Form::MIME_TYPE, 'Nahraný soubor je v nepovoleném formátu. Nahrajte prosím soubor pdf, docx nebo odt.', $mimes);
                //$f->addUpload('cl', 'Motivační dopis - nepovinné (pdf, docx, odt)')->setRequired(FALSE)
                //    ->addCondition(Form::FILLED)
                //        ->addRule(Form::MIME_TYPE, 'Nahraný soubor je v nepovoleném formátu. Nahrajte prosím soubor pdf, docx nebo odt.', $mimes);
                $f->addSubmit('send', 'Odeslat formulář');
                $f->onError[] = function(Form $f) {
                    $this->flashMessage('Ve formuláři jsou chyby, opravte je prosím a zkuste to znovu.', 'danger');
                };
                $f->onSuccess[] = function(Form $f) use ($row, $statuses, $frequencies, $id) {
                    if($f->isSubmitted() === $f['send']) {
                        $data = $f->getValues(TRUE);

                        //$cv = $data['cv'];
                        //$cl = $data['cl'];
                        /** @var FileUpload $cv */
                        /** @var FileUpload $cl */

                        $message = new Message;
                        $message->setFrom('info@volnycasdeti.cz');
                        $message->addTo('info@volnycasdeti.cz');
                        $message->setSubject('Nový zájemce o pozici ' . $row['title']);
                        //$message->addAttachment($cv->getSanitizedName(), $cv->getContents(), $cv->getContentType(), 'attachment');
                        //if($cl !== NULL && $cl->isOk()) {
                        //    $message->addAttachment($cl->getSanitizedName(), $cl->getContents(), $cl->getContentType(), 'attachment');
                        //}
                        $body = '<p><strong>Jméno a příjmení:</strong> ' . $data['name'] . '</p>';
                        $body .= '<p><strong>Věk:</strong> ' . $data['age'] . '</p>';
                        $body .= '<p><strong>Bydliště:</strong> ' . $data['home'] . '</p>';
                        $body .= '<p><strong>E-mailová adresa:</strong> ' . $data['email'] . '</p>';
                        $body .= '<p><strong>Mobil:</strong> ' . $data['phone'] . '</p>';
                        $body .= '<p><strong>Jsi student nebo pracuješ:</strong> ' . $statuses[$data['status']] . '</p>';
                        $body .= '<p><strong>Máš zájem účastnit se akcí pravidelně, nebo spíš nárazově:</strong> ' . $frequencies[$data['frequency']] . '</p>';
                        $body .= '<p><strong>Napiš nám své zkušenosti s prací s dětmi' . ($id === 'kucharka' ? ' / s vařením pro skupiny' : '') . ':</strong> ' . str_replace("\n", '<br>', $data['experience']) . '</p>';
                        $body .= '<p><strong>Proč máš zájem o tuto pozici a co od ní očekáváš:</strong> ' . str_replace("\n", '<br>', $data['motivation']) . '</p>';
                        $message->setHtmlBody($body);
                        $this->container->get(IMailer::class)->send($message);

                        $this->flashMessage('Formulář byl odeslán, děkujeme.', 'success');
                        
                        $this->container->get(Tracker::class)->addConversion('Dobrovolník', 'EZvNCPqLkYQBEMiF9YkD');
                        
                        $this->presenter->redirect('this', ['apply' => FALSE]);
                    }
                };
                $this->addComponent($f, 'form');
            }

            $this->template->row = $row;
            $this->template->apply = $apply;
        }
        $this->template->id = $id;
    }

}
