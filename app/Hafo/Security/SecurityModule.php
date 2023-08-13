<?php

namespace Hafo\Security;

use Hafo\DI\Container;
use Hafo\DI\ContainerBuilder;
use Hafo\DI\Module;
use Hafo\FileStorage\UrlAccessibleStorage;
use Hafo\Security\Authentication\Authenticator;
use Hafo\Security\Authentication\IdAuthenticator;
use Hafo\Security\Authentication\IdAuthenticator\NetteAuthenticator;
use Hafo\Security\Authentication\IdAuthenticator\NetteIdentityFactory;
use Hafo\Security\Authentication\Unauthenticator;
use Hafo\Security\Storage\Avatars;
use Hafo\Security\Storage\Emails;
use Hafo\Security\Storage\LoginTokens;
use Hafo\Security\Storage\Profiles;
use Hafo\Security\Storage\Roles;
use Hafo\Security\Storage\Users;
use Hafo\Security\Storage\Passwords;
use Nette\Application\Application;
use Nette\Database\Context;
use Nette\Http\Session;
use Nette\Security\User;

final class SecurityModule implements Module {

    private $identityFields = [];

    private $profileFields = [];

    function __construct(
        $identityFields = ['name', 'email', 'facebook_id', 'google_id', 'steam_id', 'avatar_small', 'avatar_large'],
        $profileFields = ['name', 'email', 'phone', 'city', 'street', 'zip', 'avatar_small', 'avatar_large']
    ) {
        $this->identityFields = $identityFields;
        $this->profileFields = $profileFields;
    }

    function install(ContainerBuilder $builder) {
        $builder->addFactories([
            JsSDK::class => function (Container $c) {
                return new JsSDK($c->get(User::class));
            },
            NetteAuthenticator::class => function(Container $c) {
                return new NetteAuthenticator(
                    $c->get(Users::class),
                    $c->get(LoginTokens::class),
                    $c->get(Roles::class),
                    $c->get(User::class),
                    $c->get(NetteIdentityFactory::class),
                    $c->has(Application::class) ? $c->get(Application::class) : NULL
                );
            },
            IdAuthenticator::class => function (Container $c) {
                return $c->get(NetteAuthenticator::class);
            },
            Users::class => function (Container $c) {
                return new Users\DatabaseUsers($c->get(Context::class));
            },
            LoginTokens::class => function (Container $c) {
                return new LoginTokens\DatabaseLoginTokens($c->get(Context::class));
            },
            Roles::class => function (Container $c) {
                return new Roles\DatabaseRoles($c->get(Context::class));
            },
            Emails::class => function(Container $c) {
                return new Emails\DatabaseEmails($c->get(Context::class));
            },
            Unauthenticator::class => function (Container $c) {
                return new Unauthenticator\NetteUnauthenticator(
                    $c->get(LoginTokens::class),
                    $c->get(Authentication\IdAuthenticator::class),
                    $c->get(User::class)
                );
            },
            NetteIdentityFactory::class => function (Container $c) {
                return new NetteIdentityFactory($this->identityFields);
            },
            Passwords::class => function (Container $c) {
                return new Passwords\DatabasePasswords($c->get(Context::class));
            },
            Authenticator\PasswordLogin::class => function (Container $c) {
                return new Authenticator\PasswordLogin(
                    $c->get(Passwords::class),
                    $c->get(Emails::class),
                    $c->get(Users::class),
                    $c->get(IdAuthenticator::class)
                );
            },
            Avatars::class => function(Container $c) {
                return new Avatars\DatabaseAvatars(
                    $c->get(Context::class),
                    $c->get(UrlAccessibleStorage::class)->dir('avatars')
                );
            },
            Profiles::class => function(Container $c) {
                return new Profiles\DatabaseProfiles($c->get(Context::class), $this->profileFields);
            },
            Security::class => function(Container $c) {
                return new Security($c->get(LoginTokens::class), $c->get(Unauthenticator::class));
            },
            FakeLogin::class => function(Container $c) {
                return new FakeLogin(
                    $c->get(Session::class),
                    $c->get(User::class),
                    $c->get(Unauthenticator::class),
                    $c->get(IdAuthenticator::class),
                    $c->get(Users::class)
                );
            },
        ]);
    }

}
