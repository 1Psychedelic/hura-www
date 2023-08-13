<?php

namespace VCD\Admin\Blog\UI;

use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Database\Context;
use Nette\Database\UniqueConstraintViolationException;
use Nette\Utils\Strings;
use Tomaj\Form\Renderer\BootstrapRenderer;

class CategoryControl extends Control {

    private $container;

    function __construct(ContainerInterface $container, $id = NULL) {
        $this->container = $container;
        
        $this->onAnchor[] = function() use ($id) {
            $f = new Form;
            $f->setRenderer(new BootstrapRenderer);
            $f->addText('title', 'Název');
            $f->addCheckbox('visible', 'Viditelné');
            $f->addCKEditor('content', 'Obsah');
            $f->addProtection();
            $f->addSubmit('save', 'Uložit');
            if($id !== NULL) {
                $f->addSubmit('delete', 'Odstranit')->getControlPrototype()
                    ->attrs['onclick'] = 'if(!confirm("Opravdu smazat?")){return false;}';
            }
            $f->onSuccess[] = function(Form $f) use ($id) {
                if($f->isSubmitted() === $f['save']) {
                    $data = $f->getValues(TRUE);
                    $error = 0;
                    do {
                        try {
                            $data['slug'] = Strings::webalize($data['title']) . ($error !== 0 ? '-' . $error : '');
                            if ($id !== NULL) {
                                $this->db()->table('vcd_blog_category')->wherePrimary($id)->update($data);
                            } else {
                                $last = $this->db()->table('vcd_blog_category')->select('MAX(position)')->fetchField();
                                $this->db()->table('vcd_blog_category')->insert(array_merge($data, ['position' => $last + 1]));
                            }
                            $error = 0;
                        } catch (UniqueConstraintViolationException $e) {
                            $error++;
                        }
                    } while ($error !== 0);
                    $this->presenter->flashMessage('Uloženo.', 'success');
                    $this->presenter->redirect('blogCategories');
                } else if($id !== NULL && $f->isSubmitted() === $f['delete']) {
                    $this->db()->table('vcd_blog_category')->wherePrimary($id)->delete();
                    $this->presenter->flashMessage('Smazáno.', 'success');
                    $this->presenter->redirect('blogCategories');
                }
            };
            if($id !== NULL) {
                $f->setValues($this->db()->table('vcd_blog_category')->wherePrimary($id)->fetch());
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
