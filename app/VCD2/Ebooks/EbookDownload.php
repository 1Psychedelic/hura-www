<?php

namespace VCD2\Ebooks;

use Nextras\Orm\Relationships\ManyHasOne;
use VCD2\Entity;
use VCD2\Users\User;

/**
 * @property int $id {primary}
 *
 *
 **** Základní údaje
 * @property ManyHasOne|Ebook $ebook {m:1 Ebook::$downloads}
 * @property ManyHasOne|User|NULL $user {m:1 User, oneSided=TRUE}
 * @property string $email
 *
 *
 **** Časové údaje
 * @property \DateTimeImmutable $downloadedAt {default now}
 *
 * 
 */
class EbookDownload extends Entity {

    function __construct(Ebook $ebook, User $user = NULL, $email = NULL) {
        parent::__construct();

        if(empty($email) && $user !== NULL) {
            $email = $user->email;
        }

        if(empty($email)) {
            throw new \InvalidArgumentException('E-mail not provided.');
        }

        $this->ebook = $ebook;
        $this->user = $user;
        $this->email = $email;
    }

}
