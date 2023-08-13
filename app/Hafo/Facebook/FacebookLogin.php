<?php

namespace Hafo\Facebook;

use Hafo\Security\Authentication\Authenticator;
use Hafo\Security\Authentication\IdAuthenticator;
use Hafo\Security\Authentication\LoginException;
use Hafo\Security\Storage\Users;
use Nette\Utils\Json;

final class FacebookLogin implements Authenticator  {

    static private $fields = [
        'id',
        'name',
        'first_name',
        'middle_name',
        'last_name',
        'gender',
        'link',
        'email',
        'third_party_id',
        'verified',
        'website',
        'locale'
    ];

    private $applicationId;

    private $applicationSecret;

    private $version;

    private $users;

    private $authenticator;

    private $ssl;

    function __construct($applicationId, $applicationSecret, Users $users, IdAuthenticator $authenticator, $version = '2.5', $ssl = TRUE) {
        $this->applicationId = $applicationId;
        $this->applicationSecret = $applicationSecret;
        $this->version = $version;
        $this->users = $users;
        $this->authenticator = $authenticator;
        $this->ssl = $ssl;
    }

    private function authenticate($request) {
        $me = $this->userInfo('me', $request);
        if($me && strlen($me['id']) > 1) {

            $data = [];
            if(isset($me['permissions'])) {
                $granted = $this->getGrantedPermissions($me['permissions']);
                unset($me['permissions']);
                $data = ['facebook_granted_permissions' => implode(',', $granted)];
            }

            foreach(self::$fields as $field) {
                if(array_key_exists($field, $me)) {
                    $data['facebook_' . $field] = $me[$field];
                }
            }

            if($id = $this->users->exists($me['id'], 'facebook_id')) {
                // goto login
            } else if($id = $this->users->exists($me['email'], 'email')) {
                $this->users->setUserData($id, $data);
            } else {
                $id = $this->users->register($me['email'], array_merge($data, [
                    'name' => $me['name'],
                    'email_verified' => $me['verified']
                ]));
            }
            $this->authenticator->login($id);
            return;
        }
        throw new LoginException;
    }

    function login($credentials) {
        $payload = $this->parseSignedRequest($credentials);
        if(isset($payload['code'])) {
            $accessToken = $this->accessToken($payload['code']);
            $accessToken = $this->extendAccessToken($accessToken);
            if(!$accessToken) {
                throw new LoginException('Unable to get access token.');
            }
            $this->authenticate($accessToken);
        } else {
            throw new LoginException('Unable to get access token.');
        }
    }

    private function parseSignedRequest($signedRequest) {
        if(!strpos($signedRequest, '.')) {
            \Tracy\Debugger::log($signedRequest, \Tracy\ILogger::INFO);
            throw new LoginException('Signed request is empty.');
        }
        list($encodedSignature, $payload) = explode('.', $signedRequest, 2);
        $signature = base64_decode(strtr($encodedSignature, '-_', '+/'));
        $expected = hash_hmac('sha256', $payload, $this->applicationSecret, TRUE);
        if($expected !== $signature) {
            throw new LoginException('Request signature is different from expected signature.');
        }

        return json_decode(base64_decode(strtr($payload, '-_', '+/')), TRUE);
    }

    private function accessToken($code, $redirectUri = '') {
        $accessToken = $this->tokenRequest(
            'oauth/access_token', [
                'client_id' => $this->applicationId,
                'client_secret' => $this->applicationSecret,
                'redirect_uri' => $redirectUri,
                'code' => $code
            ]
        );

        return $accessToken;
    }

    private function dataRequest($path, $params, $payload = NULL) {
        $url = $this->createUrl($path, $params);
        $response = $this->request($url, $payload);
        if($response) {
            $response = Json::decode($response, Json::FORCE_ARRAY);
        }

        return $response;
    }

    private function userInfo($userId, $accessToken) {
        $params = ['access_token' => $accessToken];
        $params['fields'] = implode(',', self::$fields);
        $response = $this->dataRequest($userId, $params);

        return $response;
    }

    private function tokenRequest($path, $params) {
        $response = $this->request($this->createUrl($path, $params));
        $parsed = Json::decode($response, Json::FORCE_ARRAY);

        return isset($parsed['access_token']) ? $parsed['access_token'] : NULL;
    }

    private function request($url, $payload = NULL) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if(!$this->ssl) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        }
        if(!is_null($payload)) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        }
        $response = curl_exec($ch);
        if(curl_errno($ch)) {
            return NULL;
        }
        curl_close($ch);

        return $response;
    }

    private function createUrl($path, $params) {
        return 'https://graph.facebook.com/'
        . 'v' . $this->version
        . '/' . $path
        . '?' . http_build_query($params);
    }

    private function extendAccessToken($accessToken) {
        $extendedToken = $this->tokenRequest(
            'oauth/access_token', [
                'grant_type' => 'fb_exchange_token',
                'client_id' => $this->applicationId,
                'client_secret' => $this->applicationSecret,
                'fb_exchange_token' => $accessToken,
                'redirect_uri' => ''
            ]
        );

        return $extendedToken ? $extendedToken : $accessToken;
    }

    private function getGrantedPermissions($permissions) {
        $granted = [];
        foreach($permissions['data'] as $permission) {
            if($permission['status'] === 'granted') {
                $granted[] = $permission['permission'];
            }
        }

        return $granted;
    }

}
