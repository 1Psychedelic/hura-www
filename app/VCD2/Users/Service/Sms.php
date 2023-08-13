<?php

namespace VCD2\Users\Service;

use VCD2\Orm;
use VCD2\Users\Consent;
use VCD2\Users\User;

class Sms {

    private $orm;

    function __construct(Orm $orm) {
        $this->orm = $orm;
    }

    /** @return User[] */
    function findConsentingUsers() {
        $users = $this->orm->users->findAll();
        $consenting = [];
        foreach($users as $user) {
            foreach($user->consents->get()->findBy(['type' => Consent::TYPE_SMS_MARKETING]) as $consent) {
                if ($consent->expiresAt > new \DateTime) {
                    $consenting[] = $user;
                    break;
                }
            }
        }

        return $consenting;
    }

}
