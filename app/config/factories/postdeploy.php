<?php

use Hafo\DI\Container;

return [

    \Hafo\PostDeploy\PostDeployScript::class => function(Container $c) {
        $list = new \Hafo\PostDeploy\PostDeployScript\PostDeployScripts;
        $list->add(new \Hafo\PostDeploy\PostDeployScript\DatabaseMigration($c->get(\Hafo\DatabaseMigration\Migrator::class)));

        //$list->add(new \VCD2\Users\Migration\EncryptionMigration($c));
        $list->add(new \Hafo\PostDeploy\PostDeployScript\DeployNotification($c->get(\VCD\Notifications\Notifications::class)));
        $list->add(new \Hafo\PostDeploy\PostDeployScript\WelcomeMessage);
        return $list;
    },

];
