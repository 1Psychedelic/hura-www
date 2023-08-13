<?php

namespace VCD2\Emails;

use Hafo\Orm\Entity\Entity;

/**
 * @property int $id {primary}
 *
 *
 **** Základní údaje
 * @property Email $email {m:1 Email::$attachments}
 * @property string $file
 *
 * 
 */
class Attachment extends Entity {

    function __construct(Email $email, $file) {
        parent::__construct();

        $this->email = $email;
        $this->file = $file;
    }

}
