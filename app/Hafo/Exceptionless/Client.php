<?php
declare(strict_types=1);

namespace Hafo\Exceptionless;

use Nette\Http\IRequest;
use Nette\Utils\Random;
use Nextras\Dbal\Utils\DateTimeImmutable;
use Throwable;

class Client
{
    /** @var string */
    private $host;

    /** @var string */
    private $apiKey;

    /** @var IRequest */
    private $request;

    /** @var \GuzzleHttp\Client */
    private $client;

    public function __construct($host, $apiKey, IRequest $request)
    {
        $this->host = $host;
        $this->apiKey = $apiKey;
        $this->request = $request;
        $this->client = new \GuzzleHttp\Client([
            'base_uri' => $host,
        ]);
    }

    public function logException(Throwable $e) {
        try {
            $this->client->request('POST', 'api/v2/events', [
                'headers' => ['Authorization' => 'Bearer ' . $this->apiKey],
                'json' => [$this->serializeException($e)],
            ]);
        } catch (Throwable $e) {
            // silent...
        }
    }

    private function serializeException(Throwable $e) {
        $date = (new DateTimeImmutable())->format('c');

        return [
            'date' => $date,
            'type' => 'error',
            'data' => [
                '@error' => [
                    'type' => get_class($e),
                    '@type' => get_class($e),
                    'message' => $e->getMessage(),
                    'stack_trace' => array_map([$this, 'serializeStackTrace'], $e->getTrace()),
                    //'stack_trace_caused_by' => $e->getPrevious() === null ? '' : $this->serializeCausedBy($e->getPrevious()),
                    //'modules' => [],
                ],
                'caused_by' => $e->getPrevious() === null ? '' : $this->serializeCausedBy($e->getPrevious()),
                '@simple_error' => [
                    'type' => get_class($e),
                    'message' => $e->getMessage(),
                    'stack_trace' => $e->getTraceAsString(),
                    'data' => [
                        'code' => $e->getCode()
                    ],
                ],
                '@request' => [
                    'user_agent' => $this->request->getHeader('User-agent'),
                    'is_secure' => $this->request->isSecured(),
                    'host' => $this->request->getRemoteHost(),
                    'port' => $this->request->isSecured() ? 443 : 80,
                    'path' => $this->request->getUrl()->getPath(),
                    'cookies' => $this->request->getCookies(),
                    'query_string' => $this->request->getUrl()->getQuery(),
                ],
                //'@submission_method' => 'onerror',
            ],
            //'tags' => [],
            //'reference_id' => substr(md5(Random::generate()), 0, 10),
        ];
    }

    private function serializeCausedBy(Throwable $e) {
        return [
            '@e_type' => get_class($e),
            '@e_message' => $e->getMessage(),
            '@stack_trace' => array_map([$this, 'serializeStackTrace'], $e->getTrace()),
            'caused_by' => $e->getPrevious() === null ? '' : $this->serializeCausedBy($e->getPrevious()),
            //'modules' => [],
        ];
    }

    private function serializeStackTrace(array $traceItem) {
        $object = '';
        if (isset($traceItem['object']) && is_object($traceItem['object'])) {
            $object = get_class($object) . $traceItem['type'];
        } elseif (isset($traceItem['class']) && is_string($traceItem['class'])) {
            $object = $traceItem['class'] . $traceItem['type'];
        }

        return [
            'name' => $object . $traceItem['function'] . '()',
            'parameters' => $traceItem['args'],
            'file_name' => $traceItem['file'] ?? '',
            'line_number' => $traceItem['line'] ?? '',
            //'column' => 1,
            //'data' => [],
        ];
    }
}
