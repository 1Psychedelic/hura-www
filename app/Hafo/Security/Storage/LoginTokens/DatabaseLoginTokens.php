<?php

namespace Hafo\Security\Storage\LoginTokens;

use Hafo\Security\Storage;
use Nette\Database\Context;

final class DatabaseLoginTokens implements Storage\LoginTokens {

    private $db;

    function __construct(Context $db) {
        $this->db = $db;
    }

    function refreshLoginToken($userId) {
        $this->db->table('system_user')->wherePrimary($userId)->update(['login_token' => \Nette\Utils\Random::generate(40)]);
    }

    function isLoginTokenValid($userId, $token) {
        if($token === NULL) {
            return FALSE;
        }
        $row = $this->db->table('system_user')->where('id = ? AND login_token = ?', [$userId, $token])->fetch();
        return $row === FALSE ? FALSE : TRUE;
    }

    function hasLoginToken($userId) {
        $row = $this->db->table('system_user')->wherePrimary($userId)->fetch();
        if($row) {
            return !empty($row['login_token']);
        }
        return FALSE;
    }

}
