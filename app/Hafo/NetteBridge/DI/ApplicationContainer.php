<?php

namespace Hafo\NetteBridge\DI;

use Hafo\DI\Container;
use Nette\Application\Request;
use Nette\Application\IPresenterFactory;
use Nette\Application\UI\Presenter;

class ApplicationContainer implements Container {

    private $container;

    function __construct(Container $container) {
        $this->container = $container;
    }

    function create($id, ...$args) {
        return $this->container->create($id);
    }

    function get($id) {
        return $this->container->get($id);
    }

    function has($id) {
        return $this->container->has($id);
    }

    /**
     * @param string $name
     * @return Presenter
     */
    function createPresenter($name) {
        $presenter = $this->get(IPresenterFactory::class)->createPresenter($name);
        $presenter->autoCanonicalize = FALSE;
        return $presenter;
    }

    /**
     * @param string $presenter
     * @param string $action
     * @param null $method
     * @param array $params
     * @param array $post
     * @param array $files
     * @return \Nette\Application\IResponse
     */
    function run($presenter, $action, $method = NULL, array $params = [], array $post = [], array $files = []) {
        return $this->createPresenter($presenter)
            ->run(new Request($presenter, $method, array_merge(['action' => $action], $params), $post, $files));
    }

}
