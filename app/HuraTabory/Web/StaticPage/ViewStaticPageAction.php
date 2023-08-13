<?php
declare(strict_types=1);

namespace HuraTabory\Web\StaticPage;

use HuraTabory\DataProvider\InitialStateDataProvider;
use HuraTabory\DataProvider\StaticPage\ViewStaticPageDataProvider;
use HuraTabory\Http\HeadersFactory;
use Latte\Engine;
use League\Route\Http\Exception\NotFoundException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\HtmlResponse;

class ViewStaticPageAction implements RequestHandlerInterface
{
    /** @var ViewStaticPageDataProvider */
    private $viewStaticPageDataProvider;

    /** @var InitialStateDataProvider */
    private $initialStateDataProvider;

    /** @var HeadersFactory */
    private $headersFactory;

    /** @var Engine */
    private $latte;

    public function __construct(
        ViewStaticPageDataProvider $viewStaticPageDataProvider,
        InitialStateDataProvider $initialStateDataProvider,
        HeadersFactory $headersFactory,
        Engine $latte
    ) {
        $this->viewStaticPageDataProvider = $viewStaticPageDataProvider;
        $this->initialStateDataProvider = $initialStateDataProvider;
        $this->headersFactory = $headersFactory;
        $this->latte = $latte;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $headers = $this->headersFactory->createDefault();

        $data = $this->initialStateDataProvider->getData($request);

        $path = $request->getUri()->getPath();
        $slug = (string)($request->getQueryParams()['slug'] ?? '');

        $staticPage = $this->viewStaticPageDataProvider->getData($slug);
        if ($staticPage === null) {
            $data['loadedStaticPages'][$path] = 404;
        } else {
            $data['loadedStaticPages'][$path] = $staticPage;
        }

        $html = $this->latte->renderToString(__DIR__ . '/view-static-page.latte', [
            'reactState' => $data,
            'staticPage' => $staticPage,
        ]);

        return new HtmlResponse($html, 200, $headers->toArray());
    }
}
