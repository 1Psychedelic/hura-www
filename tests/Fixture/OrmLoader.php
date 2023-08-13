<?php

namespace Tests\Fixture;

use Hafo\Orm\Model\RepositoryLoader;
use Nette\Caching\Cache;
use Nette\Caching\Storages\FileStorage;
use Nextras\Orm\Entity\Reflection\MetadataParserFactory;
use Nextras\Orm\Model\MetadataStorage;
use Nextras\Orm\Model\Model;
use Nextras\Orm\Model\SimpleRepositoryLoader;
use Nextras\Orm\TestHelper\TestMapper;
use VCD2\Orm;

class OrmLoader
{

    /** @var Orm|NULL */
    static private $orm;

    /** @return Orm */
    static function getOrm()
    {
        if (self::$orm === NULL) {
            $entityClassesMap = require __DIR__ . '/../../app/config/orm.php';
            $repositories = [];
            foreach ($entityClassesMap as $repositoryClass) {
                $repositories[] = new $repositoryClass(new TestMapper());
            }
            $cacheStorage = new FileStorage(__DIR__ . '/../../temp');
            $cache = new Cache($cacheStorage);
            $modelRepos = RepositoryLoader::parseAnnotations(Orm::class, $cache->derive('orm.test'));
            $configuration = Model::getConfiguration($modelRepos);
            $repositoryLoader = new SimpleRepositoryLoader($repositories);
            $metadataParserFactory = new MetadataParserFactory();
            $metadataStorage = new MetadataStorage(
                $entityClassesMap,
                $cache->derive('orm.test.metadata'),
                $metadataParserFactory,
                $repositoryLoader
            );
            self::$orm = new Orm($configuration, $repositoryLoader, $metadataStorage);
        }

        return self::$orm;
    }

}
