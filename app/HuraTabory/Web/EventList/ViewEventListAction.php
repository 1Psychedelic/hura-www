<?php
declare(strict_types=1);

namespace HuraTabory\Web\EventList;

use HuraTabory\DataProvider\InitialStateDataProvider;
use HuraTabory\Http\HeadersFactory;
use Latte\Engine;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\HtmlResponse;

class ViewEventListAction implements RequestHandlerInterface
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

        $path = $request->getUri()->getPath();

        $types = [
            '/tabory' => 'camps',
            '/vylety' => 'trips',
        ];

        $type = $types[$path] ?? 'camps';

        $headings = [
            'camps' => 'Dětské tábory',
            'trips' => 'Výlety',
        ];
        $subheadings = [
            'camps' => 'Letní tábory · Jarní tábory · Podzimní tábory',
        ];

        $data = $this->initialStateDataProvider->getData($request);

        $html = $this->latte->renderToString(__DIR__ . '/view-event-list.latte', [
            'reactState' => $data,
            'events' => $data['events'][$type] ?? [],
            'heading' => $headings[$type] ?? null,
            'subheading' => $subheadings[$type] ?? null,
        ]);

        return new HtmlResponse($html, 200, $headers->toArray());
    }
}
