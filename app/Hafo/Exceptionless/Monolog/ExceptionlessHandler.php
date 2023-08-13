<?php
declare(strict_types=1);

namespace Hafo\Exceptionless\Monolog;


use Hafo\Exceptionless\Client;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Throwable;

class ExceptionlessHandler extends AbstractProcessingHandler
{
    /** @var Client */
    private $client;

    public function __construct(Client $client, $level = Logger::DEBUG, $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->client = $client;
    }

    protected function write(array $record)
    {
        if (isset($record['context']['exception']) && $record['context']['exception'] instanceof Throwable) {
            $this->client->logException($record['context']['exception']);
        }
    }
}
