<?php

use Hafo\DI\Container;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging;
use VCD2\Orm;

return [

    \VCD\Users\Newsletter::class => function(Container $c) {
        $user = $c->get(\Nette\Security\User::class);
        $newsletter = new \VCD\Users\DefaultModel\Newsletter($c->get(\Nette\Database\Context::class));
        $newsletter->onAdd[] = function($email) use ($c, $user) {
            $c->get(\VCD\Notifications\Notifications::class)->add(
                'E-mail ' . $email . ' byl přihlášen k odběru.',
                $user->isLoggedIn() ? $user->id : NULL
            );
        };
        $newsletter->onRemove[] = function($email) use ($c, $user) {
            $c->get(\VCD\Notifications\Notifications::class)->add(
                'E-mail ' . $email . ' byl odhlášen z odběru.',
                $user->isLoggedIn() ? $user->id : NULL
            );
        };
        return $newsletter;
    },
    
    \VCD\Notifications\Notifications::class => function(Container $c) {
        return //new \VCD\Notifications\DefaultModel\CachedNotifications(
            new \VCD\Notifications\DefaultModel\Notifications(
                $c->get(\Nette\Database\Context::class),
                $c->get(\Nette\Security\User::class),
                $c->get(Hafo\Security\Storage\Roles::class),
                $c->get(Orm::class),
                $c->get(Factory::class)
            )/*,
            $c->get(\Nette\Security\User::class),
            $c->get(\Nette\Caching\Cache::class)
        )*/;
    },

];
