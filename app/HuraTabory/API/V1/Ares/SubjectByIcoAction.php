<?php
declare(strict_types=1);

namespace HuraTabory\API\V1\Ares;

use Hafo\Ares\Ares;
use Hafo\Ares\AresException;
use HuraTabory\Http\HeadersFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\JsonResponse;

class SubjectByIcoAction implements RequestHandlerInterface
{
    /** @var HeadersFactory */
    private $headersFactory;

    /** @var Ares */
    private $ares;

    public function __construct(HeadersFactory $headersFactory, Ares $ares)
    {
        $this->headersFactory = $headersFactory;
        $this->ares = $ares;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $headers = $this->headersFactory->createDefault();

        $queryParams = $request->getQueryParams();
        if (!isset($queryParams['ico'])) {
            return new EmptyResponse(400, $headers->toArray());
        }

        $subject = $this->ares->getSubjectByIco((string)$queryParams['ico']);

        if ($subject === null) {
            return new EmptyResponse(404, $headers->toArray());
        }

        return new JsonResponse([
            'name' => $subject->getName(),
            'ico' => $subject->getIco(),
            'dic' => $subject->getDic(),
            'street' => $subject->getStreet(),
            'city' => $subject->getCity(),
            'zip' => $subject->getZip(),
        ], 200, $headers->toArray());
    }
}
