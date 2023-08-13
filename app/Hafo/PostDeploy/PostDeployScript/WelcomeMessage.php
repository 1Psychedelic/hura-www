<?php

namespace Hafo\PostDeploy\PostDeployScript;

use Hafo\PostDeploy;

class WelcomeMessage implements PostDeploy\PostDeployScript {

    function run() {
        echo 'Hello world!';
    }

}
