<?php

namespace Hafo\Google;

use Hafo\Security\Authentication\Authenticator;
use Hafo\Security\Authentication\IdAuthenticator;
use Hafo\Security\Authentication\LoginException;
use Hafo\Security\Storage\Users;
use Nette\SmartObject;
use Nette\Utils\Json;

final class GoogleLogin implements Authenticator {

    use SmartObject;

    private $users;

    private $authenticator;

    private $ssl;

    function __construct(Users $users, IdAuthenticator $authenticator, $ssl = TRUE) {
        $this->users = $users;
        $this->authenticator = $authenticator;
        $this->ssl = $ssl;
    }

    function login($credentials) {
        $payload = $this->verifyToken($credentials);
        if($payload === FALSE) {
            throw new LoginException('Invalid ID token.');
        }
        $data = [
            'google_id' => $payload['sub'],
            'google_email' => $payload['email'],
            'google_name' => $payload['name']
        ];
        if($id = $this->users->exists($payload['sub'], 'google_id')) {
            // goto login
        } else if($id = $this->users->exists($payload['email'], 'email')) {
            $this->users->setUserData($id, $data);
        } else {
            $id = $this->users->register($payload['email'], array_merge($data, [
                'name' => $payload['name'],
                'email_verified' => $payload['email_verified'] === "true" ? TRUE : FALSE
            ]));
        }
        if($id) {
            $this->authenticator->login($id);
        } else {
            throw new LoginException;
        }
    }

    private function verifyToken($token) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.googleapis.com/oauth2/v3/tokeninfo?id_token=' . $token);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if(!$this->ssl) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        }
        $response = curl_exec($ch);
        if(curl_errno($ch)) {
            return NULL;
        }
        curl_close($ch);
        $data = Json::decode($response, Json::FORCE_ARRAY);
        if(isset($data['sub'])) {
            return $data;
        }
        return FALSE;
    }

}
