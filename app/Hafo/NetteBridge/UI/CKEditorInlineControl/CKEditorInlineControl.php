<?php

namespace Hafo\NetteBridge\UI;

use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use Nette\Http\IRequest;

/**
 * @method onChange($content)
 * @method onBlur($content)
 * @method onSave($content)
 */
class CKEditorInlineControl extends Control {

	public $onChange = [];

	public $onBlur = [];

    public $onSave = [];

	private $request;

	private $content;

	private $wrapperCss = '';

	private $setup = [];

    public function __construct(IRequest $request) {
	    $this->request = $request;
    }

	public function setWrapperCss($css) {
		$this->wrapperCss = $css;
		return $this;
	}

	public function addSetup($setup) {
		$this->setup[] = $setup;
		return $this;
	}

	public function setContent($content) {
		$this->content = $content;
		return $this;
	}

	public function handleChange() {
		$content = $this->loadHttpData();
		$this->onChange($content);
		$this->redrawControl();
	}

	public function handleBlur() {
		$content = $this->loadHttpData();
		$this->onBlur($content);
		$this->redrawControl();
	}

    public function handleSave() {
        $content = $this->loadHttpData();
        $this->onSave($content);
        $this->redrawControl();
    }

    public function handleFoo() {}

    public function render() {
		$this->template->setFile(__DIR__ . '/default.latte');
		$this->template->content = $this->content;
		$this->template->setup = implode(";\n", $this->setup) . ";\n";
		$this->template->wrapperCss = $this->wrapperCss;
		$this->template->render();
	}

	private function loadHttpData() {
		$this->content = $this->request->getPost($this->getUniqueId() . '-content');
		return $this->content;
	}

}

class CKEditorInlineFactory {

    private $c;

    function __construct(ContainerInterface $c) {
        $this->c = $c;
    }

    function create() {
        return new CKEditorInlineControl(
            $this->c->get(IRequest::class)
        );
    }

}
