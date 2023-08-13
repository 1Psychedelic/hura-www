<?php

namespace VCD\Admin\Website\UI;

use Hafo\Google\Analytics\Analytics;
use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Caching\Cache;
use Nette\Database\Context;
use Tomaj\Form\Renderer\BootstrapRenderer;
use Hafo\Google\UI\AdSense\AdSenseControl;

class GoogleConfigControl extends Control {

    private $container;

    function __construct(ContainerInterface $container) {
        $this->container = $container;

        $this->onAnchor[] = function() {
            $row = $this->db()->table('google_config')->fetch();
            $f = new Form;
            $f->setRenderer(new BootstrapRenderer);
            $f->addText('app_id', 'ID aplikace (app ID)')->setNullable();
            $f->addText('app_secret', 'Tajný klíč (app secret)')->setNullable();
            $f->addText('adwords_id', 'Adwords ID pro měření konverzí')
                ->setNullable()
                ->getControlPrototype()
                ->setAttribute('placeholder', 'Vypnout měření konverzí');
            $f->addText('adsense_client', 'AdSense client ID')->setNullable();
            $f->addRadioList('adsense_state', 'AdSense reklamy', [
                AdSenseControl::STATE_DISABLED => 'Vypnuté',
                AdSenseControl::STATE_ENABLED => 'Zapnuté',
                AdSenseControl::STATE_TEST_GOOGLE => 'Testovací režim',
                AdSenseControl::STATE_TEST_LOCAL => 'Vývojový režim',
            ]);
            $f->addRadioList('recaptcha_enabled', 'reCAPTCHA', [
                FALSE => 'Vypnutá',
                TRUE => 'Zapnutá',
            ]);
            $f->addText('recaptcha_site_key', 'reCAPTCHA site key')->setNullable();
            $f->addText('recaptcha_secret_key', 'reCAPTCHA secret key')->setNullable();
            $f->addRadioList('analytics_enabled', 'Analytics', [
                Analytics::STATE_DISABLED => 'Vypnuté',
                Analytics::STATE_ENABLED => 'Zapnuté',
                Analytics::STATE_TEST => 'Testovací režim',
            ]);
            $f->addText('analytics_id', 'Analytics ID')->setNullable();
            $f->addSubmit('save', 'Uložit');
            $f->onSuccess[] = function(Form $f) {
                if($f->isSubmitted() === $f['save']) {
                    $this->db()->table('google_config')->update($f->getValues(TRUE));
                    $this->container->get(Cache::class)->clean([Cache::TAGS => ['google_config']]);
                    $this->presenter->flashMessage('Uloženo.', 'success');
                    $this->presenter->redirect('this');
                }
            };
            $f->setValues($row);
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
