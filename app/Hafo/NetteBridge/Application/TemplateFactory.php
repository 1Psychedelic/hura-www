<?php

namespace Hafo\NetteBridge\Application;

use Nette\Application\UI\Control;
use Nette\Application\UI\ITemplate;
use Nette\Application\UI\ITemplateFactory;

class TemplateFactory implements ITemplateFactory {

    private $factory;

    private $filters = [];

    function __construct(ITemplateFactory $factory) {
        $this->factory = $factory;
    }

    function addFilters($filters) {
        foreach($filters as $name => $cb) {
            $this->addFilter($name, $cb);
        }
        return $this;
    }

    function addFilter($name, $cb) {
        $this->filters[$name] = $cb;
        return $this;
    }

    function createTemplate(Control $control = NULL) {
        $template = $this->factory->createTemplate($control);
        foreach($this->filters as $name => $cb) {
            $template->addFilter($name, $cb);
        }
        return $template;
    }

}
