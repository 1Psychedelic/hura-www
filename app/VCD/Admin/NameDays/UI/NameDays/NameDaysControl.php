<?php

namespace VCD\Admin\NameDays\UI;

use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use Nette\Database\Context;

class NameDaysControl extends Control {

    private $container;

    function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->nameDays = $this->container->get(Context::class)->table('name_days')->order('month ASC, day ASC');
        $this->template->render();
    }

}
