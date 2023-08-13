<?php

namespace VCD\Admin\Changelog\UI;

use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;

class ChangelogControl extends Control {

    static public $types = [
        'danger' => 'Upozornění',
        'success' => 'Nová funkce',
        'info' => 'Vylepšení',
        'warning' => 'Bugfix',
    ];

    private $container;

    function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->log = include $this->container->get('app') . '/changelog.php';
        $this->template->types = self::$types;
        $this->template->render();
    }

}
