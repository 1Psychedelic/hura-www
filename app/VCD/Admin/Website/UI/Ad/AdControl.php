<?php

namespace VCD\Admin\Website\UI;

use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Database\Context;
use Nette\Http\FileUpload;
use Nette\Utils\FileSystem;
use Tomaj\Form\Renderer\BootstrapRenderer;

class AdControl extends Control {

    private $recommendedSizes = [
        'banner' => '500×100',
        'blog' => '300×400',
    ];

    private $container;

    function __construct(ContainerInterface $container, $id) {
        $this->container = $container;

        $this->onAnchor[] = function() use ($id) {
            $f = new Form;
            $f->setRenderer(new BootstrapRenderer);
            $f->addText('url', 'URL');
            $f->addUpload('image', 'Obrázek (' . $this->recommendedSizes[$id] . ')')->addCondition(Form::FILLED)->addRule(Form::IMAGE);
            $f->addCheckbox('enabled', 'Zapnout');
            $f->addSubmit('save', 'Uložit');
            $f->onSuccess[] = function(Form $f) use ($id) {
                if($f->isSubmitted() === $f['save']) {
                    $data = $f->getValues(TRUE);
                    $dir = $this->container->get('ads') . '/' . $id;
                    FileSystem::createDir($dir);
                    $file = $data['image'];
                    /** @var FileUpload $file */
                    if($file && $file->isOk() && $file->isImage()) {
                        $path = $dir . '/' . $file->getSanitizedName();
                        $file->move($path);
                        $data['image'] = str_replace($this->container->get('www'), '', $path);
                    } else {
                        unset($data['image']);
                    }
                    $this->container->get(Context::class)->table('vcd_ad')->where('name', $id)->update($data);
                    $this->presenter->flashMessage('Uloženo.', 'success');
                    $this->presenter->redirect('this');
                }
            };
            $row = $this->container->get(Context::class)->table('vcd_ad')->where('name', $id)->fetch()->toArray();
            $this->template->image = $row['image'];
            unset($row['image']);
            $f->setValues($row);
            $this->addComponent($f, 'form');

            $this->template->clicks = $this->container->get(Context::class)->table('vcd_click')->where('url', $row['url'])->select('COUNT(id)')->fetchField();
        };
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->render();
    }

}
