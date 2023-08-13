<?php

namespace VCD\Admin\Website\UI;

use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Caching\Cache;
use Nette\Database\Context;
use Tomaj\Form\Renderer\BootstrapRenderer;

class FacebookConfigControl extends Control {

    private $container;

    function __construct(ContainerInterface $container) {
        $this->container = $container;

        $this->onAnchor[] = function() {
            $row = $this->db()->table('facebook_config')->fetch();
            $f = new Form;
            $f->setRenderer(new BootstrapRenderer);
            $f->addText('app_id', 'ID aplikace (app ID)')->setNullable();
            $f->addText('app_secret', 'Tajný klíč (app secret)')->setNullable();
            $f->addText('author_id', 'ID autora')->setNullable();
            $f->addText('pixel_id', 'ID pixelu')
                ->setNullable()
                ->getControlPrototype()
                ->setAttribute('placeholder', 'Vypnout pixel');
            $f->addSubmit('save', 'Uložit');
            $f->onSuccess[] = function(Form $f) {
                if($f->isSubmitted() === $f['save']) {
                    $this->db()->table('facebook_config')->update($f->getValues(TRUE));
                    $this->container->get(Cache::class)->clean([Cache::TAGS => ['facebook_config']]);
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
