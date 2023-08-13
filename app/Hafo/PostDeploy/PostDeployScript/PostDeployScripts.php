<?php

namespace Hafo\PostDeploy\PostDeployScript;

use Hafo\PostDeploy;

class PostDeployScripts implements PostDeploy\PostDeployScript {

    /**
     * @var PostDeploy\PostDeployScript[]
     */
    private $queue = [];

    function add(PostDeploy\PostDeployScript $script) {
        $this->queue[] = $script;
    }

    function run() {
        foreach($this->queue as $script) {
            $script->run();
        }
    }

}
