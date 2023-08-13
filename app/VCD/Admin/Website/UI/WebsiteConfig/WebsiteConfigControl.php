<?php

namespace VCD\Admin\Website\UI;

use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Database\Context;
use Tomaj\Form\Renderer\BootstrapRenderer;
use VCD2\UI\Admin\Forms\AdminFormRenderer;

class WebsiteConfigControl extends Control {

    private $container;

    function __construct(ContainerInterface $container) {
        $this->container = $container;

        $this->onAnchor[] = function() {
            $f = new Form;
            $f->setRenderer(new AdminFormRenderer());
            $f->addGroup('Kontaktní údaje');
            $f->addText('name', 'Název organizace')->setRequired();
            $f->addText('org_description', 'Popis organizace')->setRequired();
            $f->addText('contact_person', 'Kontaktní osoba')->setRequired();
            $f->addText('email', 'E-mail')->setRequired();
            $f->addText('phone', 'Telefon (+420-xxx-xxx-xxx)')->setRequired();
            $f->addText('address', 'Adresa')->setRequired();
            $f->addText('ico', 'IČO')->setRequired();
            $f->addText('bank_account', 'Číslo bankovního účtu')->setRequired();
            $f->addText('iban', 'IBAN')->setRequired();
            $f->addText('bank_name', 'Název banky')->setRequired();

            $f->addGroup('SEO');
            $f->addText('title', 'Titulek webu')->setRequired();
            $f->addText('heading', 'Nadpis na homepage')->setRequired();
            $f->addText('slogan', 'Slogan na homepage');
            $f->addTextArea('description', 'Popis (max cca 155 znaků)');
            $f->addTextArea('keywords', 'Klíčová slova oddělená čárkou');

            $f->addGroup('Odkazy na sociální sítě');
            $f->addText('facebook_link', 'Facebook')->setNullable();
            $f->addText('instagram_link', 'Instagram')->setNullable();
            $f->addText('pinterest_link', 'Pinterest')->setNullable();

            $f->addGroup('Dokumenty');
            $f->addUpload('terms_and_conditions', 'VOP');
            $f->addUpload('gdpr', 'GDPR');
            $f->addUpload('rules', 'Jak to u nás chodí');

            $f->setCurrentGroup(null);
            $f->addProtection();
            $f->addSubmit('save', 'Uložit');
            $f->onSuccess[] = function(Form $f) {
                if($f->isSubmitted() === $f['save']) {
                    $data = $f->getValues(TRUE);

                    foreach (['terms_and_conditions', 'gdpr', 'rules'] as $document) {
                        if ($data[$document]->isOk()) {
                            $filename = $data[$document]->getSanitizedName();
                            $path = $this->container->get('documents') . '/' . $filename;
                            $data[$document]->move($path);
                            $data[$document] = str_replace($this->container->get('www'), '', $path);
                        } else {
                            unset($data[$document]);
                        }
                    }

                    $this->db()->table('system_website')->update($data);
                    $this->presenter->flashMessage('Uloženo.', 'success');
                }
                $this->presenter->redirect('this');
            };
            $data = $this->db()->table('system_website')->fetch()->toArray();
            unset($data['terms_and_conditions'], $data['gdpr'], $data['rules']);
            $f->setValues($data);
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
