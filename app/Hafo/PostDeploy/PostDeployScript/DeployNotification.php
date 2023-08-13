<?php

namespace Hafo\PostDeploy\PostDeployScript;

use Hafo\PostDeploy;
use VCD\Notifications\Notifications;

class DeployNotification implements PostDeploy\PostDeployScript {

    private $notifications;

    function __construct(Notifications $notifications) {
        $this->notifications = $notifications;
    }

    function run() {
        $this->notifications->add('Byla nasazena nová verze webu.',
            null,
            null,
            Notifications::TYPE_MESSAGE,
            true
        );
    }

}
