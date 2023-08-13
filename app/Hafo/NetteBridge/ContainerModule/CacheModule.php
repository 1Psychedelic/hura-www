<?php

namespace Hafo\NetteHelpers\ContainerModule;

use Hafo\DI\Container;
use Hafo\DI\Module;
use Hafo\DI\ContainerBuilder;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\Caching\Storages\FileStorage;
use Nette\Caching\Storages\IJournal;
use Nette\Caching\Storages\SQLiteJournal;

class CacheModule implements Module {

    private $cacheDir;

    function __construct($cacheDir) {
        $this->cacheDir = $cacheDir;
    }

    function install(ContainerBuilder $builder) {

        $builder->addFactories([
            IJournal::class => function(Container $c) {
                return new SQLiteJournal($this->cacheDir . '/journal.sqlite');
            },
            IStorage::class => function(Container $c) {
                return new FileStorage(
                    $this->cacheDir,
                    $c->get(IJournal::class)
                );
            },
            Cache::class => function(Container $c) {
                return new Cache(
                    $c->get(IStorage::class)
                );
            }
        ]);

    }

}
