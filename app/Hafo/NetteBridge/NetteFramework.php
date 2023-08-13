<?php

namespace Hafo\NetteBridge;

use Hafo\DI\ContainerBuilder;
use Hafo\NetteBridge\ContainerModule\HttpModule;
use Hafo\NetteHelpers\ContainerModule\ApplicationModule;
use Hafo\NetteHelpers\ContainerModule\CacheModule;
use Hafo\NetteHelpers\ContainerModule\DatabaseModule;
use Hafo\NetteHelpers\ContainerModule\FormsModule;
use Hafo\NetteHelpers\ContainerModule\LatteModule;
use Hafo\NetteHelpers\ContainerModule\SecurityModule;

class NetteFramework {

    const PRODUCTION = TRUE;
    const DEVELOPMENT = FALSE;

    private $builder;

    function __construct(ContainerBuilder $builder) {
        $this->builder = $builder;
    }

    function installCache($cacheDir) {
        (new CacheModule($cacheDir))->install($this->builder);
        return $this;
    }

    function installForms($renderer = NULL, $translate = TRUE, $protectCSRF = TRUE) {
        (new FormsModule([
            'renderer' => $renderer,
            'protectCSRF' => $protectCSRF,
            'translate' => $translate
        ]))->install($this->builder);
        return $this;
    }

    function installHttp() {
        (new HttpModule)->install($this->builder);
        return $this;
    }

    function installLatte($tempDir, $autoRefresh = FALSE) {
        (new LatteModule($tempDir, $autoRefresh))->install($this->builder);
        return $this;
    }

    function installSecurity($namespace = NULL) {
        (new SecurityModule($namespace))->install($this->builder);
        return $this;
    }

    function installApplication($errorPresenter = 'Error', $catchExceptions = TRUE, $mapping = ['*' => 'App\*Presenter'], $translateTemplates = TRUE) {
        (new ApplicationModule([
            'errorPresenter' => $errorPresenter,
            'catchExceptions' => $catchExceptions,
            'mapping' => $mapping,
            'translateTemplates' => $translateTemplates
        ]))->install($this->builder);
        return $this;
    }

    function installDatabase($dsn, $user, $password, array $options = [\PDO::ATTR_PERSISTENT => TRUE]) {
        (new DatabaseModule($dsn, $user, $password, $options))->install($this->builder);
        return $this;
    }

    function installFramework($cacheDir, $production = self::PRODUCTION, $namespace = NULL, $mapping = ['*' => 'App\*Presenter'], $errorPresenter = 'Error') {
        $this->installHttp();
        $this->installCache($cacheDir);
        $this->installLatte($cacheDir, !$production);
        $this->installForms();
        $this->installSecurity($namespace);
        $this->installApplication($errorPresenter, $production, $mapping);
        return $this;
    }

}
