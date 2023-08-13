<?php

use Hafo\DI\Container;

return [

    \Hafo\Google\UI\AdSense\AdSenseControlFactory::class => function(Container $c) {
        $cache = $c->get(\Nette\Caching\Cache::class)->derive('google_config');
        $client = $cache->load('adsense_client', function() use ($c) {
            return $c->get(\Nette\Database\Context::class)->table('google_config')->fetch()['adsense_client'];
        });
        $state = $cache->load('adsense_state', function() use ($c) {
            return $c->get(\Nette\Database\Context::class)->table('google_config')->fetch()['adsense_state'];
        });
        return new \Hafo\Google\UI\AdSense\AdSenseControlFactory($client, $state);
    },

    \VCD\UI\FrontModule\UserModule\LoginBoxControl::class => function(Container $c) {
        return new \VCD\UI\FrontModule\UserModule\LoginBoxControl(
            $c->get(\Nette\Security\User::class),
            $c->get(\Hafo\NetteBridge\Forms\FormFactory::class),
            $c->create(Hafo\Facebook\UI\FacebookLoginComponent::class),
            $c->create(Hafo\Google\UI\GoogleLoginComponent::class),
            $c->get(\Hafo\Security\Authentication\Authenticator\PasswordLogin::class),
            $c->get(\Hafo\Security\Storage\Emails::class),
            $c->get(\VCD2\Emails\Service\Emails\EmailVerifyMail::class)
        );
    },

    \VCD\UI\FrontModule\ApplicationsModule\FeedbackControlFactory::class => function(Container $c) {
        return new \VCD\UI\FrontModule\ApplicationsModule\FeedbackControlFactory($c);
    },

    \VCD\UI\FrontModule\UserModule\AddPhotoControl::class => function(Container $c) {
        return new \VCD\UI\FrontModule\UserModule\AddPhotoControl($c->get(\Nette\Http\IRequest::class), $c->get(\Hafo\NetteBridge\Forms\FormFactory::class));
    },
    
    \VCD\UI\FrontModule\GalleryModule\LostFoundControlFactory::class => function(Container $c) {
        return new \VCD\UI\FrontModule\GalleryModule\LostFoundControlFactory($c);
    },

    \VCD\UI\FrontModule\GalleryModule\GalleryControlFactory::class => function(Container $c) {
        return new \VCD\UI\FrontModule\GalleryModule\GalleryControlFactory($c);
    },

];
