<?php

namespace VCD\Admin\Carousel\UI;

use Hafo\DI\Container;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Caching\Cache;
use Nette\Database\Context;
use Nette\Http\FileUpload;
use Nette\Utils\FileSystem;
use Tomaj\Form\Renderer\BootstrapRenderer;
use VCD\UI\FrontModule\HomepageModule\CarouselControl as FrontendCarouselControl;
use VCD2\Carousel\AbstractCarouselPage;
use VCD2\Orm;

class CarouselControl extends Control {

    private $container;

    private $orm;

    function __construct(Container $container, $carousel = 'homepage', $page = NULL, $type = 0) {
        $this->container = $container;
        $this->orm = $container->get(Orm::class);

        $this->onAnchor[] = function() use ($carousel, $page, $type) {
            $invalidate = function() {
                $cache = $this->container->get(Cache::class);
                $cache->clean([Cache::TAGS => ['vcd.carousel']]);
            };

            $db = $this->container->get(Context::class);
            $currentCarousel = $db->table('vcd_carousel')->wherePrimary($carousel)->fetch();
            if(!$currentCarousel) {
                throw new ForbiddenRequestException;
            }

            $pages = $db->table('vcd_carousel_item')->where('carousel', $carousel)->order('position ASC');
            $this->template->pages = $pages;
            $this->template->currentPage = $currentPage = $page === NULL ? NULL : $db->table('vcd_carousel_item')->where('carousel = ? AND id = ?', [$carousel, $page])->fetch();
            if($page) {
                $type = $currentPage['type'];
            }
            $this->template->type = $type;

            $f = new Form;
            $f->setRenderer(new BootstrapRenderer);
            if($type === AbstractCarouselPage::TYPE_PAGE) {
                $f->addText('link', 'Odkaz (obrázek+tlačítko)');
                $f->addText('button', 'Text tlačítka');
                $f->addTextArea('content', 'Obsah');
            } else if($type === AbstractCarouselPage::TYPE_EVENT_PAGE) {
                $f->addXSelect('related_id', 'Událost', $db->table('vcd_event')->order('starts DESC')->fetchPairs('id', 'name'));
            }
            $f->addCheckbox('visible', 'Viditelný');
            $f->addProtection();
            $f->addSubmit('save', 'Uložit');
            if($page !== NULL) {
                $f->addSubmit('delete', 'Smazat')->getControlPrototype()
                    ->attrs['onclick'] = 'if(!confirm("Opravdu smazat?")){return false;}';
                $f->addSubmit('left', 'Doleva');
                $f->addSubmit('right', 'Doprava');
            }
            $f->onSuccess[] = function(Form $f) use ($page, $currentPage, $carousel, $type, $invalidate, $db) {
                if($f->isSubmitted() === $f['save']) {
                    $data = $f->getValues(TRUE);
                    $id = NULL;
                    if ($page !== NULL) {
                        $db->table('vcd_carousel_item')->wherePrimary($page)->update($data);
                        $this->presenter->flashMessage('Uloženo.', 'success');
                    } else {
                        $data['carousel'] = $carousel;
                        $data['type'] = $type;
                        $data['position'] = (int)$db->table('vcd_carousel_item')->where('carousel', $carousel)->select('MAX(position)')->fetchField() + 1;
                        $row = $db->table('vcd_carousel_item')->insert($data);
                        $this->presenter->flashMessage('Uloženo.', 'success');
                        $id = $row['id'];
                    }
                    $invalidate();
                    $this->presenter->redirect('this', ['page' => $page === NULL ? $id : $page]);
                } else if($f->isSubmitted() === $f['delete'] && $page !== NULL) {
                    $db->table('vcd_carousel_item')->wherePrimary($page)->delete();
                    $this->presenter->flashMessage('Smazáno.', 'success');
                    $invalidate();
                    $this->presenter->redirect('this', ['page' => NULL]);
                } else if(($f->isSubmitted() === $f['left'] || $f->isSubmitted() === $f['right'] ) && $page !== NULL) {
                    $sign = $f->isSubmitted() === $f['left'] ? '<' : '>';
                    $prev = $db->table('vcd_carousel_item')->where('carousel = ? AND position ' . $sign . ' ?', [$carousel, $currentPage['position']])
                        ->order('position ' . ($sign === '<' ? 'DESC' : 'ASC'))->fetch();
                    if($prev) {
                        $db->table('vcd_carousel_item')->wherePrimary($prev['id'])->update(['position' => $currentPage['position']]);
                        $db->table('vcd_carousel_item')->wherePrimary($page)->update(['position' => $prev['position']]);
                    }
                    $invalidate();
                    $this->presenter->redirect('this');
                }
            };
            if($currentPage !== NULL) {
                $f->setValues($currentPage);
            }
            $this->addComponent($f, 'form');

            if($page !== NULL) {
                $f = new Form;
                $f->setRenderer(new BootstrapRenderer);
                $f->addUpload('img', 'Obrázek na pozadí (max 1140×370)');//->addCondition(Form::FILLED)->addRule(Form::IMAGE);
                $f->addSubmit('save', 'Nahrát');
                $f->addProtection();
                $f->onSuccess[] = function(Form $f) use ($carousel, $page, $invalidate, $db) {
                    if($f->isSubmitted() === $f['save']) {
                        $file = $f->getValues(TRUE)['img'];
                        /** @var FileUpload $file */
                        $dir = $this->container->get('carousel') . '/' . $carousel;
                        FileSystem::createDir($dir);
                        if ($file->isOk()) {
                            $ext = explode('/', $file->getContentType())[1];
                            $name = $dir . '/' . $page . '.' . $ext;
                            $file->move($name);
                            $db->table('vcd_carousel_item')->where('carousel = ? AND id = ?', [$carousel, $page])->update([
                                'background_image' => str_replace($this->container->get('www'), '', $name)
                            ]);
                            $this->presenter->flashMessage('Obrázek byl nahrán.', 'success');
                        }
                        $invalidate();
                        $this->presenter->redirect('this');
                    }
                };
                $this->addComponent($f, 'image');

                $pos = $db->table('vcd_carousel_item')->where('carousel = ? AND position <= ?', [$carousel, $currentPage['position']])->select('COUNT(id)')->fetchField();

                $carouselEntity = $this->orm->carousels->get($carousel);

                $this->addComponent(new FrontendCarouselControl($this->container, $carouselEntity->pages, $pos - 1, FALSE), 'carousel');
            }
        };
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->render();
    }

}
