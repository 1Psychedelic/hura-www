<?php

namespace VCD2\Applications;

use Hafo\Orm\Entity\Entity;
use VCD2\Events\ApplicationStep;
use VCD2\Events\ApplicationStepOption;

/**
 * @property int $id {primary}
 *
 *
 **** Základní údaje
 * @property Application $application {m:1 Application::$stepChoices}
 * @property ApplicationStep $step {m:1 ApplicationStep, oneSided=TRUE}
 * @property ApplicationStepOption $option {m:1 ApplicationStepOption, oneSided=TRUE}
 *
 *
 */
class StepChoice extends Entity {

    function __construct(Application $application, ApplicationStepOption $option) {
        parent::__construct();

        $this->application = $application;
        $this->option = $option;
        $this->step = $option->step;
    }

}
