<?php

namespace Hafo\NetteHelpers\ContainerModule;

use Hafo\DI\Container;
use Hafo\DI\Module;
use Hafo\DI\ContainerBuilder;
use Hafo\NetteHelpers\ComponentLoader;
use Hafo\NetteHelpers\FormFactory;
use Hafo\NetteHelpers\TemplateFactory\TranslatedTemplateFactory;
use Hafo\Translation\Translator;
use Nette\Application\Application;
use Nette\Application\IPresenterFactory;
use Nette\Application\IRouter;
use Nette\Application\LinkGenerator;
use Nette\Application\PresenterFactory;
use Nette\Application\Routers\RouteList;
use Nette\Application\UI\ITemplateFactory;
use Nette\Application\UI\Presenter;
use Nette\Bridges\ApplicationLatte\ILatteFactory;
use Nette\Bridges\ApplicationLatte\TemplateFactory;
use Nette\Caching\IStorage;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Nette\Http\Session;
use Nette\Security\User;
use Hafo\Utils\Arrays;
use NetteModule\MicroPresenter;

class ApplicationModule implements Module {

    private $config = [
        'errorPresenter' => 'Error',
        'catchExceptions' => TRUE,
        'mapping' => ['*' => 'App\*Presenter'],
        'inject' => FALSE
    ];

    function __construct(array $config = []) {
        foreach($config as $key => $val) {
            $this->config[$key] = $val;
        }
    }

    function install(ContainerBuilder $builder) {

        $builder->addFactories([
            Application::class => function(Container $c) {
                $app = new Application(
                    $c->get(IPresenterFactory::class),
                    $c->get(IRouter::class),
                    $c->get(IRequest::class),
                    $c->get(IResponse::class)
                );
                $app->errorPresenter = $this->config['errorPresenter'];
                $app->catchExceptions = $this->config['catchExceptions'];
                return $app;
            },
            IPresenterFactory::class => function(Container $c) {
                $factory = new PresenterFactory(function($class) use ($c) {
                    return $c->get($class);
                });
                $factory->setMapping($this->config['mapping']);
                return $factory;
            },
            LinkGenerator::class => function(Container $c) {
                return new LinkGenerator(
                    $c->get(IRouter::class),
                    $c->get(IRequest::class)->getUrl(),
                    $c->get(IPresenterFactory::class)
                );
            },
            ITemplateFactory::class => function(Container $c) {
                $factory = new TemplateFactory(
                    $c->get(ILatteFactory::class),
                    $c->get(IRequest::class),
                    $c->get(User::class),
                    $c->get(IStorage::class)
                );
                return new \Hafo\NetteBridge\Application\TemplateFactory($factory);
            },
            MicroPresenter::class => function(Container $c) {
                return new MicroPresenter(
                    NULL,
                    $c->get(IRequest::class),
                    $c->get(IRouter::class)
                );
            },
            RouteList::class => function(Container $c) {
                $router = new RouteList;
                \Tracy\Debugger::getBar()->addPanel(new \Nette\Bridges\ApplicationTracy\RoutingPanel(
                    $router,
                    $c->get(\Nette\Http\IRequest::class),
                    $c->get(\Nette\Application\IPresenterFactory::class)
                ));
                return $router;
            },
            IRouter::class => function(Container $c) {
                return $c->get(RouteList::class);
            },
        ]);

        $builder->addDecorators([
            Presenter::class => function(Presenter $presenter, Container $container) {
                $presenter->injectPrimary(
                    NULL,
                    $container->get(IPresenterFactory::class),
                    $container->get(IRouter::class),
                    $container->get(IRequest::class),
                    $container->get(IResponse::class),
                    $container->get(Session::class),
                    $container->get(User::class),
                    $container->get(ITemplateFactory::class)
                );
            }
        ]);

    }

}
