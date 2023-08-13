<?php

namespace Hafo\FileStorage;

use Hafo\DI\Container;
use Hafo\DI\ContainerBuilder;
use Hafo\DI\Module;
use Hafo\FileStorage\UrlAccessibleStorage\DefaultStorage;

final class FileStorageModule implements Module {

    private $baseDirKey;

    private $baseUrlKey;

    function __construct($baseDirKey, $baseUrlKey) {
        $this->baseDirKey = $baseDirKey;
        $this->baseUrlKey = $baseUrlKey;
    }

    function install(ContainerBuilder $builder) {
        $builder->addFactories([
            DefaultStorage::class => function(Container $c) {
                $baseDir = $c->get($this->baseDirKey);
                return new DefaultStorage($baseDir, $baseDir, $c->get($this->baseUrlKey));
            },
            UrlAccessibleStorage::class => function(Container $c) {
                return $c->get(DefaultStorage::class);
            },
            FileStorage::class => function(Container $c) {
                return $c->get(DefaultStorage::class);
            },
        ]);
    }

}
