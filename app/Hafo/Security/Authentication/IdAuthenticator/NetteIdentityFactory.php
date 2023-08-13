<?php

namespace Hafo\Security\Authentication\IdAuthenticator;

use Nette\Security\Identity;

final class NetteIdentityFactory {

    private $fields;

    function __construct(array $fields = ['name', 'facebook_id', 'google_id', 'steam_id']) {
        $this->fields = array_merge($fields, ['email', 'login_token']);
    }
    
    function create($userId, $roles = [], $data = []) {
        return new Identity(
            $userId,
            $roles,
            array_filter($data, function($key) {return in_array($key, $this->fields);}, ARRAY_FILTER_USE_KEY)
        );
    }
    
}
