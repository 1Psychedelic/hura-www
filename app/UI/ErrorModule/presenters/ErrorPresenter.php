<?php

namespace VCD\UI\ErrorModule;

use Hafo\Logger\ExceptionLogger;
use Psr\Container\ContainerInterface;
use Monolog\Logger;
use Nette\Application\Application;
use Nette\Application\BadRequestException;
use Nette\Application\Request;
use Nette\Application\UI\Presenter;
use Tracy\Debugger;
use Tracy\ILogger;
use VCD\UI\FrontModule\BasePresenter;

class ErrorPresenter extends BasePresenter {

    private $e;

    function run(Request $request) {
        $this->e = $request->getParameter('exception');
        return parent::run($request);
    }

    function startup() {
        parent::startup();

        $this->template->isErrorPage = TRUE;

        $logger = $this->logger->withName('vcd.error');
        $e = $this->e;
        if ($e instanceof BadRequestException) {
            (new ExceptionLogger($logger))->log($e, 400, sprintf('BadRequestException #%s: %s', $e->getCode(), $e->getMessage()));
            $this->template->code = $e->getCode();
        } else if($e instanceof \Throwable) {
            (new ExceptionLogger($logger))->log($e);
            $this->template->code = 500;
        } else {
            $logger->addCritical(sprintf('%s', get_class($e)));
            $this->template->code = 500;
        }
    }

}
