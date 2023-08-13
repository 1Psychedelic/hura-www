<?php

namespace Hafo\NetteBridge\UI;

use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Http\FileUpload;

/**
 * @method onUpload(FileUpload $file, DropzoneControl $control)
 * @method onDelete($file, DropzoneControl $control)
 */
class DropzoneControl extends Control {

    /**
     * @var array of function(FileUpload $file, DropzoneControl $this)
     */
    public $onUpload = [];

    /**
     * @var array of function($file, DropzoneControl $this)
     */
    public $onDelete = [];

    private $baseDir;

    private $files;

    private $thumbnailFactory;

    function __construct($baseDir, $existingFiles = []) {
        $this->baseDir = $baseDir;
        $this->files = $existingFiles;
        $f = new Form;
        $f->addUpload('file', 'Soubor', TRUE);
        $f->addSubmit('upload', 'Nahrát');
        $f->onSuccess[] = function(Form $f) {
            if(isset($f->getValues(TRUE)['file'][0]) && $f->getValues(TRUE)['file'][0]->isOk()) {
                $file = $f->getValues(TRUE)['file'][0];
                $this->onUpload($file, $this);
                if($this->thumbnailFactory !== NULL) {
                    $c = $this->thumbnailFactory;
                    $this->respond($c($file->getSanitizedName()));
                }
            }
        };
        $this->addComponent($f, 'form');
    }

    function setThumbnailFactory($thumbnailFactory) {
        $this->thumbnailFactory = $thumbnailFactory;
        return $this;
    }

    function respond($html) {
        header('Content-type: text/plain');
        die($html);
    }

    function handleDelete($file) {
        $this->onDelete($file, $this);
    }

    function attached($presenter) {
        parent::attached($presenter);
        if($presenter instanceof Presenter) {
            $this['form']->setAction($this->link('this'));
        }
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->files = $this->files;
        $this->template->baseDir = $this->baseDir;
        $this->template->thumbnailFactory = $this->thumbnailFactory;
        $this->template->render();
    }

    static function formatHtmlId($s) {
        $s = preg_replace('#-(?=[a-z])#', ' ', $s);
        $s = lcfirst(ucwords($s));
        $s = str_replace(' ', '', $s);
        return $s;
    }

}
