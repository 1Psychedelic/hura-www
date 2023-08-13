<?php

namespace VCD\Admin\MobileApp\UI;

use Hafo\DI\Container;
use Nette\Application\UI\Control;

class MobileAppPointsControl extends Control {

    const ZIP_PASSWORD = 'CxVxjRggiMYmH8kSxBh9fnms';

    const ZIP_PATH = 'files/vcd-bodovani.zip';

    private $container;

    function __construct(Container $container) {
        $this->container = $container;
    }
    
    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->zipPassword = self::ZIP_PASSWORD;
        $this->template->zipPath = self::ZIP_PATH;
        $hashFile = $this->container->get('www') . '/' . self::ZIP_PATH;
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
        $this->template->render();
    }

}
