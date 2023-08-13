<?php

namespace Hafo\Security\Storage\Emails;

use Hafo\Security\Authentication\EmailAlreadyVerifiedException;
use Hafo\Security\Authentication\InvalidEmailVerifyHashException;
use Hafo\Security\SecurityException;
use Hafo\Security\Storage;
use Nette\Database\Context;
use Nette\Utils\Random;

final class DatabaseEmails implements Storage\Emails {

    private $db;

    function __construct(Context $db) {
        $this->db = $db;
    }

    function isVerified($email) {
        $row = $this->db->table('system_user')->where('email', $email)->fetch();
        if($row) {
            return $row['email_verified'];
        }
        return FALSE;
    }

    function verify($email, $hash) {
        $row = $this->db->table('system_user')->where('email', $email)->fetch();
        if($row['email_verified']) {
            throw new EmailAlreadyVerifiedException;
        }
        if($row['email_verify_hash'] === NULL || $row['email_verify_hash'] !== $hash) {
            throw new SecurityException('Invalid e-mail/hash combination.');
        }
        $this->db->table('system_user')->where('email', $email)->update([
            'email_verified' => TRUE,
            'email_verify_hash' => NULL
        ]);
    }

    function requestEmailVerifyHash($email) {
        $row = $this->db->table('system_user')->where('email', $email)->fetch();
        if($row['email_verified']) {
            throw new EmailAlreadyVerifiedException;
        }
        $hash = $row['email_verify_hash'];
        if($hash === NULL) {
            $hash = Random::generate(40);
            $this->db->table('system_user')->where('email', $email)->update([
                'email_verify_hash' => $hash
            ]);
        }
        return $hash;
    }

}
