<?php

namespace Hafo\PostDeploy\PostDeployScript;

use Hafo\PostDeploy;

class DatabaseMigration implements PostDeploy\PostDeployScript {

    private $migration;

    function __construct(\Hafo\DatabaseMigration\Migrator $migration) {
        $this->migration = $migration;
    }

    function run() {
        $this->migration->run();
    }

}
