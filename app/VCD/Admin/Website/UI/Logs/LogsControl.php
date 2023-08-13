<?php

namespace VCD\Admin\Website\UI;

use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use Nette\Utils\Finder;

class LogsControl extends Control {

    private $container;

    function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $log = $this->container->get('app') . '/../log';
        $this->template->files = array_reverse(iterator_to_array(Finder::findFiles('*')->in($log)));
        $this->template->render();
    }

}
