<?php
declare(strict_types=1);

namespace HuraTabory\API\V1\StaticPage;

use HuraTabory\DataProvider\StaticPage\ViewStaticPageDataProvider;
use HuraTabory\Domain\StaticPage\StaticPageRepository;
use HuraTabory\Http\HeadersFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use VCD2\Orm;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\JsonResponse;

class ViewStaticPageAction implements RequestHandlerInterface
{
    /** @var ViewStaticPageDataProvider */
    private $viewStaticPageDataProvider;

    /** @var HeadersFactory */
    private $headersFactory;

    public function __construct(ViewStaticPageDataProvider $viewStaticPageDataProvider, HeadersFactory $headersFactory)
    {
        $this->viewStaticPageDataProvider = $viewStaticPageDataProvider;
        $this->headersFactory = $headersFactory;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $headers = $this->headersFactory->createDefault();

        $slug = (string)($request->getQueryParams()['slug'] ?? '');

        $data = $this->viewStaticPageDataProvider->getData($slug);

        if ($data === null) {
            return new EmptyResponse(404, $headers->toArray());
        }

        return new JsonResponse($data, 200, $headers->toArray());
    }
}
