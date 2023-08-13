<?php

namespace VCD2\Ebooks;

use Nette\Utils\Random;
use Nextras\Orm\Relationships\ManyHasOne;
use VCD2\Entity;
use VCD2\Users\User;

/**
 * @property int $id {primary}
 *
 *
 **** Základní údaje
 * @property string $hash
 * @property ManyHasOne|Ebook $ebook {m:1 Ebook::$downloads}
 * @property ManyHasOne|User|NULL $user {m:1 User, oneSided=TRUE}
 * @property string $email
 *
 *
 **** Časové údaje
 * @property \DateTimeImmutable $createdAt {default now}
 * @property \DateTimeImmutable $expiresAt {virtual}
 *
 * 
 */
class EbookDownloadLink extends Entity {

    const EXPIRATION = '+2 hours';
    const EXPIRATION_TEXT = '2 hodiny';

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
        $this->generateHash();
    }

    function createEbookDownload() {
        return new EbookDownload($this->ebook, $this->user, $this->email);
    }

    private function generateHash() {
        $this->hash = Random::generate(10);
    }

    protected function getterExpiresAt() {
        return $this->createdAt->modify(self::EXPIRATION);
    }

}
