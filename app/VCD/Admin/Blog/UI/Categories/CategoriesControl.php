<?php

namespace VCD\Admin\Blog\UI;

use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use Nette\Database\Context;
use VCD\UI\FrontModule\BlogModule\BlogPresenter;

class CategoriesControl extends Control {

    private $container;

    function __construct(ContainerInterface $container) {
        $this->container = $container;
        
        $this->onAnchor[] = function() {
            
        };
    }

    function handleCategoryUp($id) {
        $row = $this->db()->table('vcd_blog_category')->wherePrimary($id)->fetch();
        $prev = $this->db()->table('vcd_blog_category')->where('position < ?', $row['position'])->order('position DESC')->fetch();
        if($prev) {
            $this->db()->table('vcd_blog_category')->wherePrimary($prev['id'])->update(['position' => $row['position']]);
            $this->db()->table('vcd_blog_category')->wherePrimary($id)->update(['position' => $prev['position']]);
            $this->presenter->flashMessage('Pozice změněna.', 'success');
        }
        $this->presenter->redirect('this');
    }

    function handleCategoryDown($id) {
        $row = $this->db()->table('vcd_blog_category')->wherePrimary($id)->fetch();
        $next = $this->db()->table('vcd_blog_category')->where('position > ?', $row['position'])->order('position ASC')->fetch();
        if($next) {
            $this->db()->table('vcd_blog_category')->wherePrimary($next['id'])->update(['position' => $row['position']]);
            $this->db()->table('vcd_blog_category')->wherePrimary($id)->update(['position' => $next['position']]);
            $this->presenter->flashMessage('Pozice změněna.', 'success');
        }
        $this->presenter->redirect('this');
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->categories = $this->db()->table('vcd_blog_category')->order('position ASC');
        $this->template->categoryLink = BlogPresenter::LINK_DEFAULT;
        $this->template->render();
    }

    private function db() {
        return $this->container->get(Context::class);
    }

}
