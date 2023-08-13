<?php

namespace Hafo\Orm;

use Hafo\DI\Container;
use Hafo\DI\ContainerBuilder;
use Hafo\DI\Module;
use Hafo\Orm\Encryption\DatabaseCryptoHelper;
use Hafo\Orm\Entity\Reflection\MetadataParserFactory;
use Hafo\Orm\Mapper\Mapper;
use Hafo\Orm\Model\RepositoryLoader;
use Hafo\Orm\Repository\DependencyProvider;
use Nette\Caching\Cache;
use Nextras\Dbal\Bridges\NetteTracy\ConnectionPanel;
use Nextras\Dbal\Connection;
use Nextras\Dbal\IConnection;
use Nextras\Orm\Entity\Reflection\IMetadataParserFactory;
use Nextras\Orm\Model\IRepositoryLoader;
use Nextras\Orm\Model\MetadataStorage;
use Nextras\Orm\Model\Model;
use Nextras\Orm\Repository\IDependencyProvider;

class NextrasOrmModule implements Module
{
    private $config;

    private $modelClass;

    private $mapping;

    private $encryptionKeysFile;

    public function __construct(array $config, $modelClass, $mapping, $encryptionKeysFile)
    {
        $this->config = $config;
        $this->modelClass = $modelClass;
        $this->mapping = $mapping;
        $this->encryptionKeysFile = $encryptionKeysFile;
    }

    public function install(ContainerBuilder $builder)
    {
        $builder->addFactories([

            Connection::class => function (Container $c) {
                $conn = new Connection($this->config);
                ConnectionPanel::install($conn);

                return $conn;
            },

            IConnection::class => function (Container $c) {
                return $c->get(Connection::class);
            },

            Model::class => function (Container $c) {
                return $c->get($this->modelClass);
            },

            $this->modelClass => function (Container $c) {
                $class = $this->modelClass;
                $modelRepos = RepositoryLoader::parseAnnotations($class, $c->get(Cache::class)->derive('orm'));
                $configuration = Model::getConfiguration($modelRepos);

                return new $class($configuration, $c->get(IRepositoryLoader::class), $c->get(MetadataStorage::class));
            },

            MetadataStorage::class => function (Container $c) {
                return new MetadataStorage(
                    $this->mapping,
                    $c->get(Cache::class),
                    $c->get(IMetadataParserFactory::class),
                    $c->get(IRepositoryLoader::class)
                );
            },

            IMetadataParserFactory::class => function (Container $c) {
                return $c->get(MetadataParserFactory::class);
            },

            IRepositoryLoader::class => function (Container $c) {
                return $c->get(RepositoryLoader::class);
            },

            RepositoryLoader::class => function (Container $c) {
                return new RepositoryLoader($c);
            },

            IDependencyProvider::class => function (Container $c) {
                return $c->get(DependencyProvider::class);
            },

            DatabaseCryptoHelper::class => function (Container $c) {
                return new DatabaseCryptoHelper($this->encryptionKeysFile);
            },

        ]);

        $builder->addDecorators([
            Mapper::class => function (Mapper $mapper, Container $c) {
                $mapper->setCryptoHelper($c->get(DatabaseCryptoHelper::class));

                return $mapper;
            },
        ]);
    }
}
