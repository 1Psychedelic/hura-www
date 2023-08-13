<?php

namespace Hafo\Security\Storage\Users;

use Hafo\Security\SecurityException;
use Hafo\Security\Storage;
use Nette\Database\Context;
use Nette\Database\Table\ActiveRow;
use Nette\SmartObject;

/**
 * @method onRegister($userId)
 */
final class DatabaseUsers implements Storage\Users {

    use SmartObject;

    public $onRegister = [];

    private $db;

    function __construct(Context $db) {
        $this->db = $db;
    }

    function updateLastLogin($userId, \DateTimeInterface $when) {
        $this->db->table('system_user')->wherePrimary($userId)->update(['last_login' => new \DateTime]);
    }

    function exists($userId, $field = 'id') {
        if(empty($userId)) {
            return FALSE;
        }
        $row = $this->db->table('system_user')->where($field, $userId)->fetch();
        if(!$row || $row[$field] !== $userId) {
            return FALSE;
        }
        return $row->getPrimary();
    }

    function getUserData($userId, $select = '*') {
        return $this->db->table('system_user')->wherePrimary($userId)->select($select)->fetch()->toArray();
    }

    function setUserData($userId, array $data) {
        $this->db->table('system_user')->wherePrimary($userId)->update($data);
    }
    
    function register($email, array $data) {
        $row = $this->db->table('system_user')->where('email', $email)->fetch();
        if($row instanceof ActiveRow) {
            throw new SecurityException('E-mail already taken.');
        } else {
            $row = $this->db->table('system_user')->insert(array_merge($data, ['email' => $email, 'registered_at' => new \DateTime]));
            $id = $row->getPrimary();
            $this->onRegister($id);
            return $id;
        }
    }

}
