<?php

namespace VCD\Admin\Blog\UI;

use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Database\Context;
use Tomaj\Form\Renderer\BootstrapRenderer;

class ArticlePageControl extends Control {

    private $container;

    function __construct(ContainerInterface $container, $article, $id = NULL) {
        $this->container = $container;
        
        $this->onAnchor[] = function() use ($article, $id) {
            $f = new Form;
            $f->setRenderer(new BootstrapRenderer);
            $f->addCKEditor('content', 'Obsah');
            $f->addSubmit('save', 'Uložit');
            if($id !== NULL) {
                $f->addSubmit('delete', 'Odstranit')->getControlPrototype()
                    ->attrs['onclick'] = 'if(!confirm("Opravdu smazat?")){return false;}';
            }
            $f->addProtection();
            $f->onSuccess[] = function(Form $f) use ($article, $id) {
                if($f->isSubmitted() === $f['save']) {
                    $data = $f->getValues(TRUE);
                    if($id === NULL) {
                        $data['article'] = $article;
                        $data['position'] = $this->db()->table('vcd_blog_article_page')->where('article', $article)->order('position DESC')->limit(1)->select('position')->fetchField() + 1;
                        $row = $this->db()->table('vcd_blog_article_page')->insert($data);
                        $id = $row->getPrimary();
                    } else {
                        $this->db()->table('vcd_blog_article_page')->wherePrimary($id)->update($data);
                    }
                    $this->presenter->flashMessage('Uloženo.', 'success');
                    $this->presenter->redirect('blogArticlePages', ['article' => $article]);
                } else if($id !== NULL && $f->isSubmitted() === $f['delete']) {
                    $this->db()->table('vcd_blog_article_page')->wherePrimary($id)->delete();
                    $this->presenter->flashMessage('Smazáno.', 'success');
                    $this->presenter->redirect('blogArticlePages', ['article' => $article]);
                }
            };
            if($id !== NULL) {
                $f->setValues($this->db()->table('vcd_blog_article_page')->wherePrimary($id)->fetch());
            }
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
