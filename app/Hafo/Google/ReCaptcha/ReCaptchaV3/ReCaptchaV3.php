<?php

namespace Hafo\Google\ReCaptcha\ReCaptchaV3;

use GuzzleHttp\Client;
use Nette\Utils\Json;

class ReCaptchaV3 implements \Hafo\Google\ReCaptcha\ReCaptchaV3
{

    const VERIFY_ENDPOINT = 'https://www.google.com/recaptcha/api/siteverify';

    private $siteKey;

    private $secretKey;

    private $client;

    function __construct($siteKey, $secretKey, Client $client)
    {
        $this->siteKey = $siteKey;
        $this->secretKey = $secretKey;
        $this->client = $client;
    }

    function getInitLibraryHtml() {
        return '<script src="https://www.google.com/recaptcha/api.js?render=' . $this->siteKey . '"></script>';
    }

    function getInitFormHtml($action, $fieldClassName) {
        return '<script>'
            . 'grecaptcha.ready(function() {'
            . 'grecaptcha.execute(\'' . $this->siteKey . '\', {action: \'' . $action . '\'}).then(function(token) {'
            . '$(\'.' . $fieldClassName . '\').each(function(){$(this).val(token);});'
            . '});'
            . '});</script>';
    }

    function verify($token) {
        $response = $this->client->post(self::VERIFY_ENDPOINT, [
            'form_params' => [
                'secret' => $this->secretKey,
                'response' => $token,
            ]
        ]);

        $data = Json::decode($response->getBody()->getContents(), Json::FORCE_ARRAY);

        return $data['success'] === TRUE;
    }

}
