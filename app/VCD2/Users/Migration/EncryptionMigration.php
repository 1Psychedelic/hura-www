<?php

namespace VCD2\Users\Migration;

use Hafo\DI\Container;
use Hafo\PostDeploy\PostDeployScript;
use Nextras\Dbal\Connection;
use VCD2\Orm;

class EncryptionMigration implements PostDeployScript {

    private $orm;

    private $connection;

    function __construct(Container $container) {
        $this->orm = $container->get(Orm::class);
        $this->connection = $container->get(Connection::class);
    }

    function run() {
        $this->cleanChildrenWithoutApplication();

        $this->migrateUsers();
        $this->migrateChildren();
        $this->migrateApplications();
        $this->migrateApplicationChildren();
    }

    function migrateUsers() {
        foreach($this->orm->users->findAll() as $user) {
            $user->migrateEncryption();
            $this->orm->persist($user);
        }
        $this->orm->flush();
    }

    function migrateChildren() {
        foreach($this->orm->children->findAll() as $child) {
            $child->migrateEncryption();
            $this->orm->persist($child);
        }
        $this->orm->flush();
    }

    function migrateApplications() {
        foreach($this->orm->applications->findAll() as $application) {
            $application->migrateEncryption();
            $this->orm->persist($application);
        }
        $this->orm->flush();
    }

    function migrateApplicationChildren() {
        foreach($this->orm->applicationChildren->findAll() as $child) {
            $child->migrateEncryption();
            $this->orm->persist($child);
        }
        $this->orm->flush();
    }

    function cleanChildrenWithoutApplication() {
        $this->connection->query('DELETE FROM vcd_application_child WHERE application IS NULL');
    }

}
