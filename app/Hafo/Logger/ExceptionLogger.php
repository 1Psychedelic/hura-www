<?php

namespace Hafo\Logger;

use Psr\Log\LoggerInterface;
use Tracy\ILogger;

class ExceptionLogger {

    private $logger;

    function __construct(LoggerInterface $logger) {
        $this->logger = $logger;
    }

    function log($e, $level = 500, $message = NULL) {
        if($e instanceof \Throwable) {
            $file = \Tracy\Debugger::log($e, ILogger::EXCEPTION);
            $context = ['exception_log' => $file];
            $message = $message === NULL ? sprintf('%s #%s: %s', get_class($e), $e->getCode(), $e->getMessage()) : $message;
            $this->logger->log($level, $message, $context);
        } else {
            $this->logger->log($level, sprintf('%s', get_class($e)));
        }
    }

}
