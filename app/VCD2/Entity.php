<?php

namespace VCD2;

use Hafo\DI\Container;
use Monolog\Logger;
use Nette\Utils\Strings;

abstract class Entity extends \Hafo\Orm\Entity\Entity {

    /** @var Logger */
    protected $logger;

    function injectContainer(Container $container) {
        parent::injectContainer($container);
        $this->logger = $container->get(Logger::class)->withName(str_replace('-', '.', Strings::webalize(get_class($this))));
    }

    static protected function getSearchHashSalt($field) {
        return sha1($field . 'muMHVpHirhc4R3HCx67Nxj8kgJASV');
    }

}
