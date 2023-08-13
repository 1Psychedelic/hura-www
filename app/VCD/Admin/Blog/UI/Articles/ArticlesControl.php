<?php

namespace VCD\Admin\Blog\UI;

use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use Nette\Database\Context;
use VCD\UI\FrontModule\BlogModule\BlogPresenter;
use VCD\UI\FrontModule\BlogModule\PostPresenter;
use VCD2\Orm;

class ArticlesControl extends Control {

    private $container;

    function __construct(ContainerInterface $container) {
        $this->container = $container;
        
        $this->onAnchor[] = function() {
            
        };
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->articles = $this->container->get(Context::class)->table('vcd_blog_article')->order('id DESC');
        $this->template->articleLink = PostPresenter::LINK_DEFAULT;
        $this->template->users = $this->container->get(Orm::class)->users->findIdNamePairs();
        $this->template->render();
    }

}
