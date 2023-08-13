<?php

namespace VCD\Admin\Monolog\UI;

use Hafo\DI\Container;
use Monolog\Logger;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Caching\Cache;
use Nette\Database\Context;
use Tomaj\Form\Renderer\BootstrapRenderer;

class MonologConfigControl extends Control {

    private $container;

    function __construct(Container $container) {
        $this->container = $container;

        $this->onAnchor[] = function() {

            $f = new Form;
            $f->setRenderer(new BootstrapRenderer);
            $f->addText('level', 'Úroveň logování')->setRequired();
            $f->addSubmit('save', 'Uložit');
            $f->onSuccess[] = function(Form $f) {
                if($f->isSubmitted() === $f['save']) {
                    $data = $f->getValues(TRUE);
                    $this->container->get(Context::class)->table('monolog_config')->update($data);
                    $this->container->get(Cache::class)->clean([Cache::TAGS => ['monolog_config']]);
                    $this->presenter->flashMessage('Uloženo.', 'success');
                    $this->presenter->redirect('this');
                }
            };
            $f->setValues($this->container->get(Context::class)->table('monolog_config')->fetch());
            $this->addComponent($f, 'form');
        };
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->levels = Logger::getLevels();
        $this->template->render();
    }

}
