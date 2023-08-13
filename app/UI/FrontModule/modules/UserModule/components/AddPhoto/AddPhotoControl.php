<?php

namespace VCD\UI\FrontModule\UserModule;

use Hafo\NetteBridge\Forms\FormFactory;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Http\FileUpload;
use Nette\Http\IRequest;
use Tomaj\Form\Renderer\BootstrapRenderer;

/**
 * @method onUpload(FileUpload $image)
 * @method onError()
 * @method onDelete()
 * @method onGoBack()
 */
class AddPhotoControl extends Control {

    public $onUpload = [];
    
    public $onError = [];

    public $onDelete = [];

    public $onGoBack = [];
    
    private $request;
    
    function __construct(IRequest $request, FormFactory $formFactory) {
        $this->request = $request;
        
        $f = $formFactory->create();
        $f->setRenderer(new BootstrapRenderer);
        $f->addUpload('image', '');//->setRequired()->addRule(Form::IMAGE);
        $f->addSubmit('upload', 'Nahrát fotku');
        $f->addSubmit('delete', 'Vymazat fotku')->setValidationScope(FALSE);
        $f->addSubmit('back', 'Jít zpět')->setValidationScope(FALSE);
        $f->onSuccess[] = function(Form $f) {
            if($f->isSubmitted() === $f['upload']) {
                $data = $f->getValues(TRUE);
                $this->onUpload($data['image']);
            } else if($f->isSubmitted() === $f['delete']) {
                $this->onDelete();
            } else if($f->isSubmitted() === $f['back']) {
                $this->onGoBack();
            }
        };
        $this->addComponent($f, 'form');
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->render();
    }

}
