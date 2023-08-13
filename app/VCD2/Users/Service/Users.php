<?php

namespace VCD2\Users\Service;

use Hafo\Security\SecurityException;
use Nette\SmartObject;
use VCD2\Orm;
use VCD2\Users\User;

/**
 * @method onRegister(User $user)
 */
class Users implements \Hafo\Security\Storage\Users {
    
    use SmartObject;

    public $onRegister = [];

    // zpětná kompatibilita - fuck my life
    const FIELDS_MAPPING = [
        'facebook_id' => 'facebookId',
        'facebook_name' => 'facebookName',
        'facebook_first_name' => 'facebookFirstName',
        'facebook_middle_name' => 'facebookMiddleName',
        'facebook_last_name' => 'facebookLastName',
        'facebook_gender' => 'facebookGender',
        'facebook_link' => 'facebookLink',
        'facebook_email' => 'facebookEmail',
        'facebook_third_party_id' => 'facebookThirdPartyId',
        'facebook_verified' => 'facebookVerified',
        'facebook_website' => 'facebookWebsite',
        'facebook_locale' => 'facebookLocale',
        'facebook_granted_permissions' => 'facebookGrantedPermissions',
        'google_id' => 'googleId',
        'google_email' => 'googleEmail',
        'google_name' => 'googleName',
        'google_link' => 'googleLink',
    ];

    // hledání podle šifrovaných fieldů - nutno hledat podle hashe
    const FIELDS_SELECT_BY_SHA1 = [
        'facebookId' => 'hashedFacebookId',
        'googleId' => 'hashedGoogleId',
        'email' => 'hashedEmail',
    ];

    private $orm;

    function __construct(Orm $orm) {
        $this->orm = $orm;
    }

    function updateLastLogin($userId, \DateTimeInterface $when) {
        $user = $this->orm->users->get($userId);
        $user->lastLogin = new \DateTime;
        $this->orm->persistAndFlush($user);
    }

    function exists($userId, $field = 'id') {
        if(empty($userId)) {
            return FALSE;
        }

        // zpětná kompatibilita
        $searchField = $field;
        $searchValue = $userId;
        if(array_key_exists($field, self::FIELDS_MAPPING)) {
            $searchField = self::FIELDS_MAPPING[$field];
        }
        /*if(array_key_exists($field, self::FIELDS_SELECT_BY_SHA1)) {
            $searchField = self::FIELDS_SELECT_BY_SHA1[$field];
            $searchValue = User::hashForSearch($userId, $searchField);
        }*/

        foreach($this->orm->users->findBy([$searchField => $searchValue]) as $user) {
            if($user->$searchField === $userId) {
                return $user->id;
            }
        }

        return FALSE;
    }

    function getUserData($userId, $select = '*') {
        $user = $this->orm->users->get($userId);

        if($select === '*') {
            return $user->getValues();
        }

        $select = is_array($select) ? $select : explode(',', $select);
        $data = [];
        foreach($select as $field) {
            $selectField = $field;
            if(array_key_exists($field, self::FIELDS_MAPPING)) {
                $selectField = self::FIELDS_MAPPING[$field];
            }
            $data[$field] = $user->$selectField;
        }

        return $data;
    }

    function setUserData($userId, array $data) {
        $user = $this->orm->users->get($userId);

        $values = $this->modifyUserData($data);

        $user->setValues($values);
        $this->orm->persistAndFlush($user);
    }

    function register($email, array $data) {
        if($this->exists($email, 'email')) {
            throw new SecurityException('E-mail already taken.');
        }

        $values = $this->modifyUserData($data);

        $user = new User($email, isset($values['name']) ? $values['name'] : '');
        $user->setValues($values);
        $this->orm->persistAndFlush($user);

        $this->onRegister($user);

        return $user->id;
    }

    private function modifyUserData(array $data) {
        $values = [];
        foreach($data as $field => $value) {
            $setField = $field;
            if(array_key_exists($field, self::FIELDS_MAPPING)) {
                $setField = self::FIELDS_MAPPING[$field];
            }
            $values[$setField] = $value;
        }
        return $values;
    }

}
