<?php
declare(strict_types=1);

namespace HuraTabory\API\V1\Dev;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spatie\Async\Pool;
use Zend\Diactoros\Response\JsonResponse;

class TestAction implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return new JsonResponse([
            'asyncSupported' => Pool::isSupported(),
        ], 200, [
            'Cache-Control' => 'no-store, max-age=0',
        ]);
    }
}
