<?php

namespace Hafo\NetteHelpers\ContainerModule;

use Hafo\DI\Container;
use Hafo\DI\ContainerBuilder;
use Hafo\DI\Module;
use Nette\Bridges\DatabaseTracy\ConnectionPanel;
use Nette\Caching\IStorage;
use Nette\Database\Connection;
use Nette\Database\Context;
use Nette\Database\Conventions\DiscoveredConventions;
use Nette\Database\IConventions;
use Nette\Database\IStructure;
use Nette\Database\Structure;

class DatabaseModule implements Module {

    private $dsn;

    private $user;

    private $password;

    private $options;

    function __construct($dsn, $user, $password, array $options = [\PDO::ATTR_PERSISTENT => TRUE]) {
        $this->dsn = $dsn;
        $this->user = $user;
        $this->password = $password;
        $this->options = $options;
    }

    function install(ContainerBuilder $builder) {

        $builder->addFactories([
            Connection::class => function (Container $c) {
                $connection = new Connection(
                    $this->dsn,
                    $this->user,
                    $this->password,
                    $this->options
                );
                \Tracy\Debugger::getBar()->addPanel(new ConnectionPanel($connection), 'db');
                return $connection;
            },
            \PDO::class => function(Container $c) {
                return $c->get(Connection::class)->getPdo();
            },
            IStructure::class => function (Container $c) {
                return new Structure(
                    $c->get(Connection::class),
                    $c->get(IStorage::class)
                );
            },
            IConventions::class => function(Container $c) {
                return new DiscoveredConventions(
                    $c->get(IStructure::class)
                );
            },
            Context::class => function(Container $c) {
                return new Context(
                    $c->get(Connection::class),
                    $c->get(IStructure::class),
                    $c->get(IConventions::class),
                    $c->get(IStorage::class)
                );
            }
        ]);

    }

}
