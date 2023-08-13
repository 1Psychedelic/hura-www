<?php

namespace VCD\Admin\Blog\UI;

use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use Nette\Database\Context;

class ArticlePagesControl extends Control {

    private $container;

    private $article;

    function __construct(ContainerInterface $container, $article) {
        $this->container = $container;

        $this->article = $article;

        $this->onAnchor[] = function() use ($article) {
            $this->template->article = $article;
            $this->template->list = $this->db()->table('vcd_blog_article_page')->where('article', $article)->order('position ASC');
        };
    }

    function handleUp($id) {
        $row = $this->db()->table('vcd_blog_article_page')->wherePrimary($id)->fetch();
        $prev = $this->db()->table('vcd_blog_article_page')->where('article = ? AND position < ?', [$this->article, $row['position']])->order('position DESC')->fetch();
        if($prev) {
            $this->db()->table('vcd_blog_article_page')->wherePrimary($prev['id'])->update(['position' => $row['position']]);
            $this->db()->table('vcd_blog_article_page')->wherePrimary($id)->update(['position' => $prev['position']]);
            $this->presenter->flashMessage('Pozice změněna.', 'success');
        }
        $this->presenter->redirect('this');
    }

    function handleDown($id) {
        $row = $this->db()->table('vcd_blog_article_page')->wherePrimary($id)->fetch();
        $next = $this->db()->table('vcd_blog_article_page')->where('article = ? AND position > ?', [$this->article, $row['position']])->order('position ASC')->fetch();
        if($next) {
            $this->db()->table('vcd_blog_article_page')->wherePrimary($next['id'])->update(['position' => $row['position']]);
            $this->db()->table('vcd_blog_article_page')->wherePrimary($id)->update(['position' => $next['position']]);
            $this->presenter->flashMessage('Pozice změněna.', 'success');
        }
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
