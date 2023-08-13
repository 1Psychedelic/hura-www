<?php
declare(strict_types=1);

namespace HuraTabory\Web\Games;

use HuraTabory\DataProvider\InitialStateDataProvider;
use HuraTabory\Http\HeadersFactory;
use Latte\Engine;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\HtmlResponse;

class ViewGamesAction implements RequestHandlerInterface
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

        $data = $this->initialStateDataProvider->getData($request);

        $html = $this->latte->renderToString(__DIR__ . '/view-games.latte', [
            'reactState' => $data,
            'games' => $data['games']
        ]);

        return new HtmlResponse($html, 200, $headers->toArray());
    }
}
