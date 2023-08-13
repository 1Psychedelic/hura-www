<?php

namespace VCD\Admin\Blog\UI;

use Hafo\NetteBridge\UI\DropzoneControl;
use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Database\Context;
use Nette\Database\UniqueConstraintViolationException;
use Nette\Http\FileUpload;
use Nette\Security\User;
use Nette\Utils\FileSystem;
use Nette\Utils\Finder;
use Nette\Utils\Html;
use Nette\Utils\Strings;
use Tomaj\Form\Renderer\BootstrapRenderer;
use VCD2\Orm;

class ArticleControl extends Control {

    private $container;

    function __construct(ContainerInterface $container, $id = NULL) {
        $this->container = $container;

        $this->onAnchor[] = function() use ($id) {
            $categories = $this->db()->table('vcd_blog_category')->order('position ASC')->fetchPairs('id', 'title');
            $authors = $this->container->get(Orm::class)->users->findAdminIdNamePairs();
            $f = new Form;
            $f->setRenderer(new BootstrapRenderer);
            $f->addText('title', 'Nadpis');
            $f->addXSelect('category', 'Kategorie', $categories)->setPrompt('Nezařazené');
            $f->addXSelect('author', 'Autor', $authors);
            $f->addDateTimePicker('published_at', 'Zveřejněno');
            $f->addCKEditor('perex', 'Perex');
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
                    if($data['published_at'] === NULL) {
                        $data['published_at'] = new \DateTime;
                    }
                    $error = 0;
                    do {
                        try {
                            $data['slug'] = Strings::webalize($data['title']) . ($error !== 0 ? '-' . $error : '');
                            if ($id !== NULL) {
                                $this->db()->table('vcd_blog_article')->wherePrimary($id)->update($data);
                            } else {
                                $this->db()->table('vcd_blog_article')->insert($data);
                            }
                            $error = 0;
                        } catch (UniqueConstraintViolationException $e) {
                            $error++;
                        }
                    } while ($error !== 0);
                    $this->presenter->flashMessage('Uloženo.', 'success');
                    $this->presenter->redirect('blogArticles');
                } else if($id !== NULL && $f->isSubmitted() === $f['delete']) {
                    $this->db()->table('vcd_blog_article')->wherePrimary($id)->delete();
                    $this->presenter->flashMessage('Smazáno.', 'success');
                    $this->presenter->redirect('blogArticles');
                }
            };
            if($id === NULL) {
                $f['author']->setValue($this->container->get(User::class)->getId());
            } else {
                $f->setValues($this->db()->table('vcd_blog_article')->wherePrimary($id)->fetch());
            }
            $this->addComponent($f, 'form');
            $this->template->id = $id;

            if($id !== NULL) {
                $this->addComponent($this->dropzone($this->container->get('blog') . '/' . $id), 'dropzone');
            }
        };
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->render();
    }

    private function dropzone($dir) {
        $d = new DropzoneControl($this->container->get('www'), file_exists($dir) ? Finder::findFiles('*')->in($dir) : []);
        $d->setThumbnailFactory($this->dropzoneTemplate($dir, $d));
        $d->onUpload[] = function(FileUpload $file, DropzoneControl $control) use ($dir) {
            FileSystem::createDir($dir);
            $filename = $file->getSanitizedName();
            $file->move($dir . '/' . $filename);
        };
        $d->onDelete[] = function($filename, DropzoneControl $control) use ($dir) {
            FileSystem::delete($dir . '/' . $filename);
            $this->presenter->redirect('this');
        };
        return $d;
    }

    private function dropzoneTemplate($dir, DropzoneControl $control) {
        return function($name) use ($dir, $control) {
            return Html::el()->addHtml(
                Html::el('a')->href($this->template->baseUri . str_replace($this->container->get('www'), '', $dir . '/' . $name))->target('blank')->setText($name)
            )->addHtml(
                Html::el('br')
            )->addHtml(
                Html::el('a')->href($control->link('delete!', ['file' => $name]))->setText('Smazat')
            );
        };
    }

    private function db() {
        return $this->container->get(Context::class);
    }

}
