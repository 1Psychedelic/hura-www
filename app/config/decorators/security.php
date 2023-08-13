<?php

use Hafo\DI\Container;

return [

    \Hafo\Security\Storage\Users\DatabaseUsers::class => function(\Hafo\Security\Storage\Users\DatabaseUsers $users, Container $c) {

        $users->onRegister[] = function($userId) use ($c) {

            // Registrace - Google konverze
            $c->get(\Hafo\Google\ConversionTracking\Tracker::class)->addConversion('Registrace na webu', 'VWOxCKnOgoQBEMiF9YkD');

        };

    },

    \VCD2\Users\Service\AutomaticSignup::class => function(\VCD2\Users\Service\AutomaticSignup $automaticSignup, Container $c) {

        $automaticSignup->onSignup[] = function(\VCD2\Users\User $user) use ($c) {

            // Registrace - Google konverze
            $c->get(\Hafo\Google\ConversionTracking\Tracker::class)->addConversion('Registrace na webu', 'VWOxCKnOgoQBEMiF9YkD');

        };

    },

    \VCD2\Users\Service\Users::class => function(\VCD2\Users\Service\Users $users, Container $c) {

        $users->onRegister[] = function(\VCD2\Users\User $user) use ($c) {

            // Registrace - Google konverze
            $c->get(\Hafo\Google\ConversionTracking\Tracker::class)->addConversion('Registrace na webu', 'VWOxCKnOgoQBEMiF9YkD');

        };

    }

];
