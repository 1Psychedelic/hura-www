<?php

namespace VCD2\Emails;

use Hafo\Orm\Entity\Entity;
use Nextras\Orm\Relationships\OneHasMany;

/**
 * @property int $id {primary}
 *
 *
 **** Základní údaje
 * @property string $name
 * @property string $message
 * @property OneHasMany|Attachment[] $attachments {1:m Attachment::$email}
 *
 * 
 */
class Email extends Entity {

    function __construct($name, $message) {
        parent::__construct();

        $this->name = $name;
        $this->message = $message;
    }

}
