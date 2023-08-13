<?php

use Hafo\DI\Container;

return [

    'facebook.appId' => function(Container $c) {
        $cache = $c->get(\Nette\Caching\Cache::class)->derive('facebook_config');
        return $cache->load('app_id', function(&$dependencies) use ($c) {
            $dependencies[\Nette\Caching\Cache::TAGS] = ['facebook_config'];
            return $c->get(\Nette\Database\Context::class)->table('facebook_config')->fetch()['app_id'];
        });
    },

    'facebook.appSecret' => function(Container $c) {
        $cache = $c->get(\Nette\Caching\Cache::class)->derive('facebook_config');
        return $cache->load('app_secret', function(&$dependencies) use ($c) {
            $dependencies[\Nette\Caching\Cache::TAGS] = ['facebook_config'];
            return $c->get(\Nette\Database\Context::class)->table('facebook_config')->fetch()['app_secret'];
        });
    },

    'facebook.authorId' => function(Container $c) {
        $cache = $c->get(\Nette\Caching\Cache::class)->derive('facebook_config');
        return $cache->load('author_id', function(&$dependencies) use ($c) {
            $dependencies[\Nette\Caching\Cache::TAGS] = ['facebook_config'];
            return $c->get(\Nette\Database\Context::class)->table('facebook_config')->fetch()['author_id'];
        });
    },

    'facebook.pixelId' => function(Container $c) {
        $cache = $c->get(\Nette\Caching\Cache::class)->derive('facebook_config');
        return $cache->load('pixel_id', function(&$dependencies) use ($c) {
            $dependencies[\Nette\Caching\Cache::TAGS] = ['facebook_config'];
            return $c->get(\Nette\Database\Context::class)->table('facebook_config')->fetch()['pixel_id'];
        });
    },

    \Hafo\Facebook\FacebookPixel\FacebookPixel::class => function(Container $c) {
        $pixelId = $c->get('facebook.pixelId');

        if($pixelId === NULL) {
            return new \Hafo\Facebook\FacebookPixel\FacebookPixel\NoPixel();
        }

        return new Hafo\Facebook\FacebookPixel\FacebookPixel\FacebookPixel(
            $c->get(\Nette\Http\Session::class),
            $pixelId,
            $c->get(\VCD2\Users\Service\UserContext::class)->getEntity()
        );
    },

];
