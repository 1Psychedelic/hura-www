<?php

namespace VCD2\Utils\Temporary;

use Nette\Utils\Strings;
use VCD2\Orm;

class EmailsCaseSensitivity {

    private $orm;

    function __construct(Orm $orm) {
        $this->orm = $orm;
    }

    public function findAffectedEmails() {
        $allUsers = $this->orm->users->findAll();
        $emails = [];
        foreach($allUsers as $user) {
            $emails[$user->email] = Strings::lower($user->email);
        }

        return array_diff_assoc($emails, array_unique($emails));
    }

    public function findEmailsWithUppercase() {
        $allUsers = $this->orm->users->findAll();
        $emails = [];
        foreach($allUsers as $user) {
            if($user->email !== Strings::lower($user->email)) {
                $emails[$user->email] = $user->email;
            }
        }

        return $emails;
    }

}
