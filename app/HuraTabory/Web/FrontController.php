<?php
declare(strict_types=1);

namespace HuraTabory\Web;

use Hafo\DI\Container;
use Hafo\Exceptionless\Client;
use HuraTabory\Web\Authentication\CompleteSignupAction;
use HuraTabory\Web\Authentication\VerifyEmailAction;
use HuraTabory\Web\Blank\ViewBlankAction;
use HuraTabory\Web\Event\ViewEventAction;
use HuraTabory\Web\Games\ViewGamesAction;
use HuraTabory\Web\Sitemap\ViewSitemapAction;
use HuraTabory\Web\StaticPage\ViewStaticPageAction;
use HuraTabory\Web\EventList\ViewEventListAction;
use HuraTabory\Web\Home\ViewHomeAction;
use League\Route\Http\Exception\NotFoundException;
use League\Route\Router;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\HttpHandlerRunner\Emitter\EmitterInterface;

class FrontController
{
    private const ROUTES = [
        '/tabor/{event}' => [
            'GET' => ViewEventAction::class,
        ],
        '/tabor/{event}/rezervace' => [
            'GET' => ViewBlankAction::class,
        ],
        '/tabor/{event}/rezervace/{step}' => [
            'GET' => ViewBlankAction::class,
        ],
        '/tabor/{event}/{tab}' => [
            'GET' => ViewEventAction::class,
        ],
        '/vylet/{event}' => [
            'GET' => ViewEventAction::class,
        ],
        '/vylet/{event}/rezervace' => [
            'GET' => ViewBlankAction::class,
        ],
        '/vylet/{event}/rezervace/{step}' => [
            'GET' => ViewBlankAction::class,
        ],
        '/vylet/{event}/{tab}' => [
            'GET' => ViewEventAction::class,
        ],
        '/tabory' => [
            'GET' => ViewEventListAction::class,
        ],
        '/vylety' => [
            'GET' => ViewEventListAction::class,
        ],
        '/nase-stolni-hry' => [
            'GET' => ViewGamesAction::class,
        ],
        '/overit-email' => [
            'GET' => VerifyEmailAction::class,
        ],
        '/aktivovat-ucet' => [
            'GET' => CompleteSignupAction::class,
        ],
        '/stranka/{slug}' => [
            'GET' => ViewStaticPageAction::class,
        ],
        '/sitemap.xml' => [
            'GET' => ViewSitemapAction::class,
        ],
        '/' => [
            'GET' => ViewHomeAction::class,
        ],
    ];

    private $container;

    private $request;

    private $emitter;

    private $exceptionlessClient;

    public function __construct(
        Container $container,
        ServerRequestInterface $request,
        EmitterInterface $emitter,
        Client $exceptionlessClient
    ) {
        $this->container = $container;
        $this->request = $request;
        $this->emitter = $emitter;
        $this->exceptionlessClient = $exceptionlessClient;
    }

    public function run(): void
    {
        if ($this->request->getUri()->getScheme() === 'http') {
            $this->emitter->emit(new RedirectResponse(
                $this->request->getUri()->withScheme('https'),
                301
            ));

            return;
        }

        $router = new Router();

        foreach (self::ROUTES as $mask => $routeDefinition) {
            foreach ($routeDefinition as $method => $action) {
                $router->map($method, $mask, function (ServerRequestInterface $request, array $args) use ($action): ResponseInterface {
                    $request = $request->withQueryParams(array_merge($request->getQueryParams(), $args));

                    $handler = $this->container->get($action);

                    if ($handler instanceof RequestHandlerInterface) {
                        return $handler->handle($request);
                    }

                    return new EmptyResponse(500);
                });
            }
        }

        try {
            $this->emitter->emit($router->dispatch($this->request));
        } catch (NotFoundException $e) {
            $this->emitter->emit($this->container->get(ViewBlankAction::class)->handle($this->request));
        }
    }
}
