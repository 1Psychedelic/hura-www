<?php

namespace Hafo\NetteHelpers\ContainerModule;

use Hafo\DI\Container;
use Hafo\DI\ContainerBuilder;
use Hafo\DI\Module;
use Nette\Bridges\SecurityTracy\UserPanel;
use Nette\Http\Session;
use Nette\Http\UserStorage;
use Nette\Security\IUserStorage;
use Nette\Security\User;

class SecurityModule implements Module {

    private $namespace;

    function __construct($namespace = NULL) {
        $this->namespace = $namespace === NULL ? substr(md5(__DIR__), 0, 10) : $namespace;
    }

    function install(ContainerBuilder $builder) {
        $builder->addFactories([
            User::class => function(Container $c) {
                $user = new User(
                    $c->get(IUserStorage::class)
                );
                $user->setExpiration(0, FALSE, TRUE);
                \Tracy\Debugger::getBar()->addPanel(new UserPanel($user), 'user');
                return $user;
            },
            IUserStorage::class => function(Container $c) {
                $storage = new UserStorage(
                    $c->get(Session::class)
                );
                $storage->setNamespace($this->namespace);
                return $storage;
            }
        ]);
    }

}
