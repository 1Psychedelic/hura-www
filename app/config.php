<?php

use Hafo\DI\Autowiring\AutowiringCache\NetteCache;
use Hafo\DI\Autowiring\DefaultAutowiring;
use Hafo\NetteBridge\NetteFramework;
use Hafo\DI\ContainerBuilder;
use Hafo\DI\Container;
use Nette\Caching\Cache;
use Nette\Utils\FileSystem;

date_default_timezone_set('Europe/Prague');
setlocale(LC_ALL, 'cs_CZ.UTF-8');

$dir = __DIR__;
$server = '/';
$path = '';
$tmp = $dir . '/../temp/cache';
$www = $dir . '/../public';
$upload = $www . '/upload';
$storage = $dir . '/../storage';
$dev = DEV;

FileSystem::createDir($tmp);

$cacheStorage = new \Nette\Caching\Storages\FileStorage($tmp);

$builder = new ContainerBuilder();

$builder->addParameters([
    'base' => $server . $path,
    'server' => $server,
    'path' => $path,
    'dev' => $dev,
    'tmp' => $tmp,
    'app' => $dir,
    'vendor' => $dir . '/../vendor',
    'www' => $www,

    'profileImages' => $upload . '/user-content/profiles',
    'childrenImages' => $upload . '/user-content/children',
    'events' => $upload . '/events',
    'carousel' => $upload . '/carousel',
    'page' => $upload . '/page',
    'games' => $upload . '/games',
    'photos' => $upload . '/photos',
    'lostfound' => $upload . '/lostfound',
    'diplomas' => $upload . '/diplomas',
    'letters' => $upload . '/letters',
    'leaders' => $upload . '/leaders',
    'emails' => $upload . '/emails',
    'facebook-images' => $upload . '/fb',
    'blog' => $upload . '/blog',
    'ebooks' => $upload . '/ebooks',
    'ads' => $upload . '/ads',
    'recruitment' => $upload . '/recruitment',
    'documents' => $upload . '/documents',

    'invoices' => $storage . '/invoices',
]);

$builder->setAutowiring(new DefaultAutowiring(new NetteCache(new Cache($cacheStorage, 'autowiring'))));

if (file_exists($dir . '/config/local/db.php')) {
    $db = require_once($dir . '/config/local/db.php');
} else {
    $db = require_once($dir . '/config/db.php');
}

(new NetteFramework($builder))
    ->installDatabase(sprintf('mysql:host=%s;dbname=%s', $db['host'], $db['database']), $db['username'], $db['password'])
    ->installHttp()
    ->installCache($tmp)
    ->installLatte($tmp, $dev)
    ->installForms()
    ->installSecurity('VCD')
    ->installApplication('Error:Error', !$dev, ['*' => 'VCD\UI\*Module\*Presenter']);

/** @var \Hafo\DI\Module[] $modules */
$modules = [
    new \Hafo\FileStorage\FileStorageModule('www', 'base'),
    new \Hafo\Facebook\FacebookLoginModule('facebook.appId', 'facebook.appSecret'),
    new \Hafo\Google\GoogleLoginModule('google.appId'),
    new \Hafo\Security\SecurityModule(['name', 'email', 'facebook_id', 'google_id', 'avatar_small', 'avatar_large']),
    new \Hafo\Orm\NextrasOrmModule([
        'driver' => 'mysqli',
        'host' => $db['host'],
        'username' => $db['username'],
        'password' => $db['password'],
        'database' => $db['database'],
        'connectionTz' => 'auto-offset',
        'charset' => 'utf8',
    ], \VCD2\Orm::class, require $dir . '/config/orm.php', $dir . '/config/crypto.php'),
    new \Hafo\Translation\TranslationModule(['cs', 'en'], function (Container $c) {
        return 'cs';
    }),
    new \Hafo\Cron\CronModule,
];
foreach ($modules as $module) {
    $module->install($builder);
}

$cache = new Cache($cacheStorage, 'config');
$loader = function ($type) use ($dir, $dev) {
    return function (&$dependencies) use ($dir, $dev, $type) {
        $files = array_keys(iterator_to_array(\Nette\Utils\Finder::findFiles('*.php')->from($dir . '/config/' . $type)));
        if ($dev) {
            $dependencies[Cache::FILES] = $files;
        }

        return $files;
    };
};
$factories = $cache->load('factories', $loader('factories'));
$decorators = $cache->load('decorators', $loader('decorators'));
$factoriesLocal = $cache->load('factories_local', $loader('local/factories'));

foreach ($factories as $factory) {
    $builder->addFactories(require $factory);
}
foreach ($factoriesLocal as $factoryLocal) {
    $builder->addFactories(require $factoryLocal);
}
foreach ($decorators as $decorator) {
    $builder->addDecorators(require $decorator);
}

require 'config/netteform.php';

return $builder;
