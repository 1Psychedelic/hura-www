<?php

use Hafo\DI\Container;

return [

    'db.fullAccess' => function(Container $c) {
        $cacheStorage = $c->get(\Nette\Caching\IStorage::class);
        if (file_exists(__DIR__ . '/../local/db-full.php')) {
            $db = include __DIR__ . '/../local/db-full.php';
        } else {
            $db = include __DIR__ . '/../db-full.php';
        }
        $connection = new \Nette\Database\Connection(
            sprintf('mysql:host=%s;dbname=%s', $db['host'], $db['database']),
            $db['username'],
            $db['password']
        );
        //\Tracy\Debugger::getBar()->addPanel(new \Nette\Bridges\DatabaseTracy\ConnectionPanel($connection), 'db.full'); // debug only
        $structure = new \Nette\Database\Structure($connection, $cacheStorage);
        $conventions = new \Nette\Database\Conventions\DiscoveredConventions($structure);
        return new \Nette\Database\Context($connection, $structure, $conventions, $cacheStorage);
    },

];
