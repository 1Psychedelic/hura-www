<?php

namespace VCD2\Events;

use Hafo\Orm\Entity\Entity;
use Nextras\Orm\Collection\ICollection;
use Nextras\Orm\Relationships\OneHasMany;

/**
 * @property int $id {primary}
 *
 *
 **** Základní údaje
 * @property Event $event {m:1 Event::$steps}
 * @property OneHasMany|ApplicationStepOption[] $options {1:m ApplicationStepOption::$step, orderBy=[position=ASC]}
 * @property string $tab
 * @property string $slug
 * @property string $prompt
 * @property bool $multiple {default FALSE}
 * @property bool $required {default TRUE}
 * @property int $position
 *
 * 
 */
class ApplicationStep extends Entity {

    function __construct(Event $event, $tab, $prompt, $multiple = FALSE, $required = TRUE) {
        parent::__construct();

        $this->event = $event;
        $this->tab = $tab;
        $this->prompt = $prompt;
        $this->multiple = $multiple;
        $this->required = $required;

        $this->position = 0;
        //$topStep = $step->steps->get()->orderBy(['position' => ICollection::DESC])->fetch();
        //$this->position = $topStep === NULL ? 0 : $topStep->position + 1;
    }

}
