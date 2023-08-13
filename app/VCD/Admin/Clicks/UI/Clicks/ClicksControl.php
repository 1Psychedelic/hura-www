<?php

namespace VCD\Admin\Clicks\UI;

use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Database\Context;
use Tomaj\Form\Renderer\BootstrapRenderer;

class ClicksControl extends Control {

    private $container;

    private $url;

    function __construct(ContainerInterface $container, $url = NULL) {
        $this->container = $container;
        $this->url = $url;
        
        $this->onAnchor[] = function() use ($url) {
            $f = new Form;
            $f->setRenderer(new BootstrapRenderer);
            $f->addText('url', 'URL')->addCondition(Form::FILLED)->addRule(Form::URL);
            $f->addProtection();
            $f->addSubmit('getLink', 'Zobrazit měřící odkaz');
            $f->onSuccess[] = function(Form $f) {
                if($f->isSubmitted() === $f['getLink']) {
                    $this->presenter->redirect('this', ['url' => $f->getValues(TRUE)['url']]);
                }
            };
            if($url !== NULL) {
                $f['url']->setValue($url);
            }
            $this->addComponent($f, 'form');
        };
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->list = $this->container->get(Context::class)->table('vcd_click')->select('url,COUNT(id) AS clicks')->group('url');
        $this->template->url = $this->url;
        $this->template->action = \VCD\UI\FrontModule\WebModule\ClickPresenter::LINK_DEFAULT;
        $this->template->render();
    }

}
