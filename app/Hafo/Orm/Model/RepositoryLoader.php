<?php

namespace Hafo\Orm\Model;

use Hafo\DI\Container;
use Nette\Caching\Cache;
use Nette\Utils\Reflection;
use Nextras\Orm\Model\IRepositoryLoader;
use Nextras\Orm\Repository\IRepository;

class RepositoryLoader implements IRepositoryLoader
{
    private $container;

    private $cache = [];

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function hasRepository(string $className) : bool
    {
        return $this->container->has($className);
    }

    public function getRepository(string $className) : IRepository
    {
        if (!array_key_exists($className, $this->cache)) {
            $this->cache[$className] = $this->container->get($className);
        }

        return $this->cache[$className];
    }

    public function isCreated(string $className) : bool
    {
        return array_key_exists($className, $this->cache);
    }

    public static function parseAnnotations($modelClass, Cache $cache)
    {
        return $cache->load($modelClass, function () use ($modelClass) {
            $modelReflection = new \ReflectionClass($modelClass);

            $repositories = [];
            preg_match_all(
                '~^  [ \t*]*  @property(?:|-read)  [ \t]+  ([^\s$]+)  [ \t]+  \$  (\w+)  ()~mx',
                (string)$modelReflection->getDocComment(), $matches, PREG_SET_ORDER
            );

            foreach ($matches as list(, $type, $name)) {
                $type = Reflection::expandClassName($type, $modelReflection);
                if (!class_exists($type)) {
                    throw new \RuntimeException("Repository '{$type}' does not exist.");
                }

                $repositories[$name] = $type;
            }

            return $repositories;
        });
    }
}
