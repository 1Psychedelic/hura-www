<?php

use Google\Client;
use Hafo\DI\Container;

return [

    'google.appId' => function(Container $c) {
        $cache = $c->get(\Nette\Caching\Cache::class)->derive('google_config');
        return $cache->load('app_id', function(&$dependencies) use ($c) {
            $dependencies[\Nette\Caching\Cache::TAGS] = ['google_config'];
            return $c->get(\Nette\Database\Context::class)->table('google_config')->fetch()['app_id'];
        });
    },

    'google.adwordsId' => function(Container $c) {
        $cache = $c->get(\Nette\Caching\Cache::class)->derive('google_config');
        return $cache->load('adwords_id', function(&$dependencies) use ($c) {
            $dependencies[\Nette\Caching\Cache::TAGS] = ['google_config'];
            return $c->get(\Nette\Database\Context::class)->table('google_config')->fetch()['adwords_id'];
        });
    },

    'google.recaptchaEnabled' => function(Container $c) {
        $cache = $c->get(\Nette\Caching\Cache::class)->derive('google_config');
        return $cache->load('recaptcha_enabled', function(&$dependencies) use ($c) {
            $dependencies[\Nette\Caching\Cache::TAGS] = ['google_config'];
            return $c->get(\Nette\Database\Context::class)->table('google_config')->fetch()['recaptcha_enabled'];
        });
    },

    'google.recaptchaSiteKey' => function(Container $c) {
        $cache = $c->get(\Nette\Caching\Cache::class)->derive('google_config');
        return $cache->load('recaptcha_site_key', function(&$dependencies) use ($c) {
            $dependencies[\Nette\Caching\Cache::TAGS] = ['google_config'];
            return $c->get(\Nette\Database\Context::class)->table('google_config')->fetch()['recaptcha_site_key'];
        });
    },

    'google.recaptchaSecretKey' => function(Container $c) {
        $cache = $c->get(\Nette\Caching\Cache::class)->derive('google_config');
        return $cache->load('recaptcha_secret_key', function(&$dependencies) use ($c) {
            $dependencies[\Nette\Caching\Cache::TAGS] = ['google_config'];
            return $c->get(\Nette\Database\Context::class)->table('google_config')->fetch()['recaptcha_secret_key'];
        });
    },

    'google.analyticsId' => function (Container $c) {
        $cache = $c->get(\Nette\Caching\Cache::class)->derive('google_config');
        return $cache->load('analytics_id', function(&$dependencies) use ($c) {
            $dependencies[\Nette\Caching\Cache::TAGS] = ['google_config'];
            return $c->get(\Nette\Database\Context::class)->table('google_config')->fetch()['analytics_id'];
        });
    },

    'google.analyticsEnabled' => function (Container $c) {
        $cache = $c->get(\Nette\Caching\Cache::class)->derive('google_config');
        return $cache->load('analytics_enabled', function(&$dependencies) use ($c) {
            $dependencies[\Nette\Caching\Cache::TAGS] = ['google_config'];
            return $c->get(\Nette\Database\Context::class)->table('google_config')->fetch()['analytics_enabled'];
        });
    },

    Client::class => function (Container $c) {
        return new Client(['client_id' => $c->get('google.appId')]);
    },

    \Hafo\Google\Analytics\Analytics::class => function (Container $c) {
        $analyticsEnabled = $c->get('google.analyticsEnabled');

        if ($analyticsEnabled === \Hafo\Google\Analytics\Analytics::STATE_ENABLED) {
            return new \Hafo\Google\Analytics\Analytics\Analytics(
                $c->get(\Nette\Http\Session::class),
                $c->get('google.analyticsId')
            );
        }

        if ($analyticsEnabled === \Hafo\Google\Analytics\Analytics::STATE_TEST) {
            return new \Hafo\Google\Analytics\Analytics\TestAnalytics($c->get(\Nette\Http\Session::class));
        }

        return new \Hafo\Google\Analytics\Analytics\NoAnalytics();
    },

    \Hafo\Google\ReCaptcha\ReCaptchaV3::class => function(Container $c) {
        $recaptchaEnabled = $c->get('google.recaptchaEnabled');

        if(!$recaptchaEnabled) {
            return new \Hafo\Google\ReCaptcha\ReCaptchaV3\NoReCaptchaV3();
        }

        return new \Hafo\Google\ReCaptcha\ReCaptchaV3\ReCaptchaV3(
            $c->get('google.recaptchaSiteKey'),
            $c->get('google.recaptchaSecretKey'),
            new \GuzzleHttp\Client(['verify' => $c->get('ssl.verify')])
        );
    },

    \Hafo\Google\ConversionTracking\Tracker::class => function(Container $c) {
        $adwordsId = $c->get('google.adwordsId');

        if($adwordsId === NULL) {
            return new \Hafo\Google\ConversionTracking\Tracker\NoTracker();
        }

        return new \Hafo\Google\ConversionTracking\Tracker\Tracker(
            $c->get(\Nette\Http\Session::class),
            $adwordsId
        );
    },

];
