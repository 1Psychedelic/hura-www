<?php

namespace VCD\Admin\Users\UI;

use Hafo\DI\Container;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Tomaj\Form\Renderer\BootstrapRenderer;
use VCD2\Users\Service\Sms;
use VCD2\Utils\PhoneNumbers;

class SmsControl extends Control {

    private $container;

    function __construct(Container $container, array $users = [], $message = NULL) {
        $this->container = $container;

        $this->onAnchor[] = function() use ($users, $message) {

            $consentingUsers = $this->container->get(Sms::class)->findConsentingUsers();
            $options = [];
            $selected = [];
            foreach($consentingUsers as $consentingUser) {
                $options[$consentingUser->id] = '#' . $consentingUser->id . ' ' . $consentingUser->name . ' - ' . $consentingUser->phone;
                if(count($users) === 0 || in_array($consentingUser->id, $users)) {
                    $selected[] = $consentingUser->id;
                }
            }

            $f = new Form;
            $f->setRenderer(new BootstrapRenderer);
            $f->addCheckboxList('users', 'Uživatelé', $options);
            $f->addtextArea('message', 'Zpráva');
            $f->addSubmit('show', 'Vytvořit odkaz');
            $f->onSuccess[] = function(Form $f) {
                if($f->isSubmitted() === $f['show']) {
                    $data = $f->getValues(TRUE);
                    $this->presenter->redirect('this', ['users' => implode(' ', $data['users']), 'message' => $data['message']]);
                }
            };
            $f->setValues(['users' => $selected, 'message' => $message]);
            $this->addComponent($f, 'form');

            if(!empty($users)) {
                $phoneNumbers = [];
                foreach($consentingUsers as $consentingUser) {
                    if(in_array($consentingUser->id, $users)) {
                        $phoneNumbers[] = PhoneNumbers::normalize($consentingUser->phone);
                    }
                }
                $this->template->phoneNumbers = array_unique($phoneNumbers);
                $this->template->message = $message;
            }
        };
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->render();
    }

}
