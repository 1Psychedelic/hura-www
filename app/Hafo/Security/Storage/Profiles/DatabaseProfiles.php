<?php

namespace Hafo\Security\Storage\Profiles;

use Nette\Database\Context;
use Hafo\Security\Storage;

final class DatabaseProfiles implements Storage\Profiles {

    private $database;

    private $fields;

    function __construct(Context $database, $fields = ['name', 'email', 'phone', 'city', 'street', 'zip', 'avatar_small', 'avatar_large']) {
        $this->database = $database;
        $this->fields = array_merge($fields, ['save_profile']);
    }

    function save($userId, array $data) {
        $this->database->table('system_user')->wherePrimary($userId)->update(array_intersect_key($data, array_flip($this->fields)));
    }

    function load($userId) {
        return $this->database->table('system_user')->select(implode(',', $this->fields))->wherePrimary($userId)->fetch()->toArray();
    }

}
