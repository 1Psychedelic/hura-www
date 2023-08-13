<?php

use Hafo\DI\Container;
use VCD2\Users\Service\AutomaticSignup;
use VCD2\Users\User;

return [

    \Hafo\Security\Storage\Users::class => function(Container $c) {
        $users = $c->get(\VCD2\Users\Service\Users::class);

        // automaticky spárovat existující přihlášky + zaregistrovat děti
        $users->onRegister[] = function(User $user) use ($c) {
            $c->get(AutomaticSignup::class)->pairApplications($user->email);
        };

        return $users;
    },

    \Hafo\Security\Storage\Emails::class => function(Container $c) {
        return $c->get(\VCD2\Users\Service\Emails::class);
    },

    \Hafo\Security\Storage\Passwords::class => function(Container $c) {
        return $c->get(\VCD2\Users\Service\Passwords::class);
    },

];
