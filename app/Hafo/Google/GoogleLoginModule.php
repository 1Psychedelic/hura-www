<?php

namespace Hafo\Google;

use Hafo\DI\Container;
use Hafo\DI\ContainerBuilder;
use Hafo\DI\Module;
use Hafo\Google\UI\GoogleLoginComponent;
use Hafo\Security\Authentication\IdAuthenticator;
use Hafo\Security\Storage\Users;

final class GoogleLoginModule implements Module {

    private $appIdKey;

    private $ssl;

    function __construct($appIdKey, $ssl = TRUE) {
        $this->appIdKey = $appIdKey;
        $this->ssl = $ssl;
    }

    function install(ContainerBuilder $builder) {
        $builder->addFactories([
            GoogleSDK::class => function(Container $c) {
                return new GoogleSDK($c->get($this->appIdKey));
            },
            GoogleLogin::class => function(Container $c) {
                return new GoogleLogin($c->get(Users::class), $c->get(IdAuthenticator::class), $this->ssl);
            },
            GoogleLoginComponent::class => function(Container $c) {
                return new GoogleLoginComponent($c->get(GoogleLogin::class));
            },
        ]);
    }

}
