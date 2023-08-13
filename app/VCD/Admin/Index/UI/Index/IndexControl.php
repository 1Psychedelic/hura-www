<?php

namespace VCD\Admin\Index\UI;

use Hafo\NameDays\NameDays;
use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use VCD\Admin\Applications\NewApplications;

class IndexControl extends Control  {

    private $container;

    function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->names = $this->container->get(NameDays::class)->namesAt(new \DateTime);
        $hashFile = $this->container->get('app') . '/.hash';
        $lastDeploy = file_exists($hashFile) ? filemtime($hashFile) : NULL;
        if($lastDeploy !== NULL) {
            $lastDeploy = (new \DateTime)->setTimestamp($lastDeploy);
            $today = new \DateTime;
            if($today->format('j. n. Y') === $lastDeploy->format('j. n. Y')) {
                $lastDeploy = 'dnes ' . $lastDeploy->format('H:i');
            } else if($today->modify('-1 day')->format('j. n. Y') === $lastDeploy->format('j. n. Y')) {
                $lastDeploy = 'včera ' . $lastDeploy->format('H:i');
            }
        }
        $this->template->lastDeploy = $lastDeploy;
        $this->template->newApplications = $this->container->get(NewApplications::class)->count();
        $this->template->render();
    }

}
