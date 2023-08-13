<?php

namespace Hafo\Facebook;

use Hafo\DI\Container;
use Hafo\DI\ContainerBuilder;
use Hafo\DI\Module;
use Hafo\Facebook\UI\FacebookLoginComponent;
use Hafo\Security\Authentication\IdAuthenticator;
use Hafo\Security\Storage\Users;

final class FacebookLoginModule implements Module {

    private $appIdKey;

    private $appSecretKey;

    private $version;

    private $ssl;

    private $locale;

    function __construct($appIdKey, $appSecretKey, $version = '2.5', $ssl = TRUE, $locale = 'cs_CZ') {
        $this->appIdKey = $appIdKey;
        $this->appSecretKey = $appSecretKey;
        $this->version = $version;
        $this->ssl = $ssl;
        $this->locale = $locale;
    }

    function install(ContainerBuilder $builder) {
        $builder->addFactories([
            FacebookSDK::class => function(Container $c) {
                return new FacebookSDK($c->get($this->appIdKey), $this->version, $this->locale);
            },
            FacebookLogin::class => function(Container $c) {
                return new FacebookLogin(
                    $c->get($this->appIdKey),
                    $c->get($this->appSecretKey),
                    $c->get(Users::class),
                    $c->get(IdAuthenticator::class),
                    $this->version,
                    $this->ssl
                );
            },
            FacebookAvatars::class => function(Container $c) {
                return new FacebookAvatars;
            },
            FacebookLoginComponent::class => function(Container $c) {
                return new FacebookLoginComponent($c->get(FacebookLogin::class));
            },
        ]);
    }

}
