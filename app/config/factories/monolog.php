<?php

use Hafo\DI\Container;
use Hafo\Exceptionless\Client;
use Hafo\Exceptionless\Monolog\ExceptionlessHandler;

return [
    
    \Psr\Log\LoggerInterface::class => function(Container $c) {
        return $c->get(\Monolog\Logger::class);
    },

    'monolog.logLevel' => function(Container $c) {
        $cache = $c->get(\Nette\Caching\Cache::class)->derive('monolog_config');
        return $cache->load('level', function(&$dependencies) use ($c) {
            $dependencies[\Nette\Caching\Cache::TAGS] = ['monolog_config'];
            return $c->get(\Nette\Database\Context::class)->table('monolog_config')->fetch()['level'];
        });
    },
    
    \Monolog\Logger::class => function(Container $c) {

        $level = $c->get('monolog.logLevel');
        $level = $level === NULL ? \Monolog\Logger::ERROR : $level;

        //$fields = ['user', 'action', 'params', 'ajax', 'request_uuid'];
        $fields = [
            'user' => '%?i',
            'action' => '%?s',
            'params' => '%?s',
            'ajax' => '%b',
            'request_uuid' => '%s',
            'ip' => '%s',
            'referer' => '%?s',
            'request_path' => '%?s',
            'request_method' => '%?s',
            'exception_log' => '%?s',
        ];

        //$handler = new \MySQLHandler\MySQLHandler($c->get('db.fullAccess')->getConnection()->getPdo(), 'monolog', $fields);
        //$handler = new \Hafo\Monolog\DatabaseHandler($c->get(\Nette\Database\Context::class), 'monolog', $fields);
        $handler = new \Hafo\Monolog\NextrasHandler($c->get(\Nextras\Dbal\Connection::class), 'monolog', $fields, $level);

        $logger = new \Monolog\Logger('vcd');
        $logger->pushHandler(new ExceptionlessHandler($c->get(Client::class), \Monolog\Logger::ERROR));
        $logger->pushHandler($handler);

        $uuid = \Nette\Utils\Random::generate(64);

        $logger->pushProcessor(function ($record) use ($c, $uuid) {

            $app = $c->get(\Nette\Application\Application::class);
            $presenter = $app->getPresenter();

            // find most appropriate request to log
            $requests = $app->getRequests();

            $httpRequest = $c->get(\Nette\Http\Request::class);

            if(count($requests) > 0) {

                /** @var \Nette\Application\Request $lastRequest */
                $lastRequest = $requests[count($requests) - 1];
                $i = 1;
                while($i <= count($requests)) {
                    $lastRequest = $requests[count($requests) - $i];
                    if($lastRequest->getPresenterName() !== $app->errorPresenter && $lastRequest->getMethod() !== \Nette\Application\Request::FORWARD) {
                        break;
                    }
                    $i++;
                }

                $user = $c->get(\Nette\Security\User::class);
                $record['user'] = $user->isLoggedIn() ? $user->getId() : NULL;

                $params = $lastRequest->getParameters();
                $action = \Nette\Utils\Arrays::pick($params, \Nette\Application\UI\Presenter::ACTION_KEY, '');
                $record['action'] = $lastRequest->getPresenterName() . ':' . $action;
                $record['params'] = \Nette\Utils\Json::encode($params);
            }

            $record['request_path'] = $httpRequest->getUrl()->getBasePath() . $httpRequest->getUrl()->getRelativeUrl();
            $record['request_method'] = $httpRequest->getMethod();
            $record['ajax'] = $httpRequest->isAjax();
            $record['ip'] = $httpRequest->getRemoteAddress();
            $record['referer'] = $httpRequest->getReferer() === NULL ? NULL : (string)$httpRequest->getReferer();
            $record['request_uuid'] = $uuid;

            return $record;
        });

        return $logger;
    },
    
];
