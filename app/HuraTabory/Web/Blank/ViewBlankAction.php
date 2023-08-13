<?php
declare(strict_types=1);

namespace HuraTabory\Web\Blank;

use HuraTabory\DataProvider\InitialStateDataProvider;
use HuraTabory\Http\HeadersFactory;
use Latte\Engine;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\HtmlResponse;

class ViewBlankAction implements RequestHandlerInterface
{
    /** @var Engine */
    private $latte;

    /** @var InitialStateDataProvider */
    private $initialStateDataProvider;

    /** @var HeadersFactory */
    private $headersFactory;

    public function __construct(
        Engine $latte,
        InitialStateDataProvider $initialStateDataProvider,
        HeadersFactory $headersFactory
    ) {
        $this->latte = $latte;
        $this->initialStateDataProvider = $initialStateDataProvider;
        $this->headersFactory = $headersFactory;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $headers = $this->headersFactory->createDefault();

        $html = $this->latte->renderToString(__DIR__ . '/view-blank.latte', [
            'reactState' => $this->initialStateDataProvider->getData($request),
        ]);

        return new HtmlResponse($html, 404, $headers->toArray());
    }
}
