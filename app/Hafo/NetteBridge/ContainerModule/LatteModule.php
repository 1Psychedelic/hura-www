<?php

namespace Hafo\NetteHelpers\ContainerModule;

use Hafo\DI\Container;
use Hafo\DI\ContainerBuilder;
use Hafo\DI\Module;
use Hafo\NetteBridge\Latte\LatteFactory;
use Latte\Compiler;
use Latte\Engine;
use Nette\Bridges\ApplicationLatte\ILatteFactory;
use Nette\Bridges\FormsLatte\FormMacros;

class LatteModule implements Module {

    private $tempDir;

    private $autoRefresh = FALSE;

    function __construct($tempDir, $autoRefresh = FALSE) {
        $this->tempDir = $tempDir;
        $this->autoRefresh = $autoRefresh;
    }

    function install(ContainerBuilder $builder) {
        $builder->addFactories([
            LatteFactory\ContainerLatteFactory::class => function(Container $c) {
                return new LatteFactory\ContainerLatteFactory($c);
            },
            LatteFactory::class => function(Container $c) {
                return $c->get(LatteFactory\ContainerLatteFactory::class);
            },
            ILatteFactory::class => function(Container $c) {
                return $c->get(LatteFactory::class);
            },
            Engine::class => function(Container $c) {
                $latte = new Engine;
                FormMacros::install($latte->getCompiler());
                $latte->setTempDirectory($this->tempDir);
                $latte->setAutoRefresh($this->autoRefresh);
                $latte->setContentType(Compiler::CONTENT_HTML);
                return $latte;
            }
        ]);
    }

}
