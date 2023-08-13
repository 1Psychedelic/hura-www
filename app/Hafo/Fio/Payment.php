<?php

namespace Hafo\Fio;

use Hafo\Orm\Entity\Entity;

/**
 * @property int $id {primary}
 * @property string $fioId
 * @property int $amount
 * @property string|NULL $variableSymbol
 * @property string|NULL $comment
 * @property string|NULL $message
 */
class Payment extends Entity {

    function __construct($fioId, $amount, $variableSymbol = NULL, $comment = NULL, $message = NULL) {
        parent::__construct();
        
        $this->fioId = $fioId;
        $this->amount = $amount;
        $this->variableSymbol = $variableSymbol;
        $this->comment = $comment;
        $this->message = $message;
    }

}
