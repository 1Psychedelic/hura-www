<?php

namespace Hafo\Security\Storage\Passwords;

use Hafo\Security\Storage;
use Nette\Database\Context;
use Nette\Security\Passwords as BCrypt;
use Nette\Utils\Random;

final class DatabasePasswords implements Storage\Passwords {

    private $db;

    function __construct(Context $db) {
        $this->db = $db;
    }

    function requestPasswordResetHash($userId) {
        $hash = Random::generate(40);
        $this->db->table('system_user')->wherePrimary($userId)->update([
            'password_restore' => $hash
        ]);
        return $hash;
    }

    function isPasswordResetHashValid($hash) {
        if(!$hash) {
            return FALSE;
        }
        $user = $this->db->table('system_user')->where('password_restore', $hash)->fetch();

        return !$user ? false : (int)$user['id'];
    }

    function setPassword($userId, $passwordPlain) {
        $this->db->table('system_user')->wherePrimary($userId)->update([
            'password' => BCrypt::hash($passwordPlain),
            'password_restore' => NULL
        ]);
    }

    function verifyPassword($email, $passwordPlain) {
        $row = $this->db->table('system_user')->where('email', $email)->fetch();
        if(!$row || !$row['password']) {
            return FALSE;
        }
        if(BCrypt::verify($passwordPlain, $row['password'])) {
            return $row['id'];
        }
        return FALSE;
    }

}
