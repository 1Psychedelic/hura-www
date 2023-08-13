<?php

namespace VCD\Admin\Newsletter\UI;

use Hafo\DI\Container;
use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Database\Context;
use Nette\Utils\Validators;
use Tomaj\Form\Renderer\BootstrapRenderer;
use VCD\Users\Newsletter;

class NewsletterBlacklistControl extends Control {

    private $container;

    function __construct(Container $container) {
        $this->container = $container;

        $this->onAnchor[] = function() {

            $f = new Form;

            $f->addTextArea('emails', 'E-maily (1 na řádek)');
            $f->setRenderer(new BootstrapRenderer);
            $f->addSubmit('add', 'Přidat do blacklistu');
            $f->onSuccess[] = function (Form $f) {
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
                            $this->container->get(Newsletter::class)->remove($email);
                            $this->container->get(Context::class)->table('vcd_newsletter_blacklist')->insert([
                                'email' => $email,
                                'added_at' => new \DateTime,
                            ]);
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
                        $this->presenter->flashMessage($added . ' e-mailů bylo přidáno do blacklistu.', 'success');
                        $this->presenter->redirect('this');
                    }
                }
            };
            $this->addComponent($f, 'form');

            $this->template->emails = $this->container->get(Context::class)->table('vcd_newsletter_blacklist')->order('id DESC');

        };
    }

    function handleRemove($id) {
        $this->container->get(Context::class)->table('vcd_newsletter_blacklist')->wherePrimary($id)->delete();
        $this->presenter->flashMessage('E-mail byl odebrán z blacklistu.');
        $this->redirect('this');
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->render();
    }

}
