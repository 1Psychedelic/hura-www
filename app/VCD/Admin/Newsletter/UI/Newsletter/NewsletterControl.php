<?php

namespace VCD\Admin\Newsletter\UI;

use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Database\Context;
use Nette\Database\DriverException;
use Nette\Utils\Validators;
use Tomaj\Form\Renderer\BootstrapRenderer;
use VCD\Users\Newsletter;
use VCD2\Orm;

class NewsletterControl extends Control {

    private $container;

    private $orm;

    function __construct(ContainerInterface $container, $divider = ';') {
        $this->container = $container;
        $this->orm = $container->get(Orm::class);

        $this->onAnchor[] = function() use ($divider) {

            $this->template->divider = $divider;

            $f = new Form;
            $f->setRenderer(new BootstrapRenderer);
            $f->addTextArea('emails', 'E-maily (1 na řádek)');
            $f->addSubmit('add', 'Přihlásit k newsletteru');
            $f->onSuccess[] = function(Form $f) {
                if($f->isSubmitted() === $f['add']) {
                    $data = $f->getValues(TRUE);
                    $emails = [];
                    if(strpos($data['emails'], "\n") === FALSE) {
                        $emails = [$data['emails']];
                    } else {
                        $emails = array_unique(array_filter(array_map(function($val) {return trim($val);}, explode("\n", $data['emails']))));
                    }
                    if(empty($emails)) {
                        $f['emails']->addError('Nebyl zadán žádný e-mail.');
                        return;
                    }
                    $error = 0;
                    $added = 0;
                    foreach($emails as $email) {
                        if(!Validators::isEmail($email)) {
                            $error++;
                        } else {
                            $this->container->get(Newsletter::class)->add($email);
                            $added++;
                        }
                    }

                    if($error > 0) {
                        $this->presenter->flashMessage($error . ' e-mailů je ve špatném formátu.', 'danger');
                        if($added === 0) {
                            $f['emails']->addError($error . ' e-mailů je ve špatném formátu.');
                            return;
                        }
                    }
                    if($added > 0) {
                        $this->presenter->flashMessage($added . ' e-mailů bylo přidáno.', 'success');
                        $this->presenter->redirect('this');
                    }
                }
            };
            $this->addComponent($f, 'form');

            $blacklist = $this->db()->table('vcd_newsletter_blacklist')->fetchPairs(NULL, 'email');

            $selection = $this->db()->table('vcd_newsletter');

            $this->template->emails = $selection->order('added_at DESC');

            if(!empty($blacklist)) {
                $selection->where('email NOT IN ?', $blacklist);
            }
            $subscribers = $selection->fetchPairs(NULL, 'email');

            $emails = [];
            foreach($selection as $email) {
                $emails[$email['email']] = TRUE;
            }

            foreach($this->orm->users->findAll() as $user) {
                if(strlen($user->email) > 0 && !in_array($user->email, $blacklist, TRUE)) {
                    $emails[$user->email] = TRUE;
                }
            }

            $attendants = [];
            foreach($this->orm->applications->findAll() as $application) {
                if(strlen($application->email) > 0 && !in_array($application->email, $blacklist, TRUE)) {
                    $emails[$application->email] = TRUE;
                    if(in_array($application->email, $subscribers, TRUE)) {
                        $attendants[$application->email] = TRUE;
                    }
                }
            }


            $this->template->allEmails = array_filter(array_keys($emails));

            $this->template->attendants = array_keys($attendants);
        };
    }

    function handleUnsubscribe($id) {
        $this->container->get(Newsletter::class)->remove($id);
        $this->presenter->flashMessage('E-mail byl smazán z newsletteru.', 'success');
        $this->presenter->redirect('this');
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->render();
    }

    private function db() {
        return $this->container->get(Context::class);
    }

}
