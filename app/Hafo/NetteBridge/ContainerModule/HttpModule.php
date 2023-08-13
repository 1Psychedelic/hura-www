<?php

namespace Hafo\NetteBridge\ContainerModule;

use Hafo\DI\Container;
use Hafo\DI\ContainerBuilder;
use Hafo\DI\Module;
use Nette\Http\IResponse;
use Nette\Bridges\HttpTracy\SessionPanel;
use Nette\Http\Context;
use Nette\Http\IRequest;
use Nette\Http\Request;
use Nette\Http\RequestFactory;
use Nette\Http\Response;
use Nette\Http\Session;

class HttpModule implements Module {

    function install(ContainerBuilder $builder) {
        $builder->addFactories([
            IRequest::class => function (Container $c) {
                return $c->get(Request::class);
            },
            Request::class => function (Container $c) {
                return (new RequestFactory)->createHttpRequest();
            },
            IResponse::class => function (Container $c) {
                return $c->get(Response::class);
            },
            Response::class => function(Container $c) {
                return new Response;
            },
            Session::class => function(Container $c) {
                $session = new Session(
                    $c->get(IRequest::class),
                    $c->get(IResponse::class)
                );
                if(class_exists(\Tracy\Debugger::class)) {
                    \Tracy\Debugger::getBar()->addPanel(new SessionPanel, 'session');
                }
                return $session;
            },
            Context::class => function(Container $c) {
                return new Context(
                    $c->get(IRequest::class),
                    $c->get(IResponse::class)
                );
            }
        ]);
    }

}