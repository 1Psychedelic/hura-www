<?php
declare(strict_types=1);

namespace HuraTabory\Web\Event;

use DateTimeImmutable;
use HuraTabory\DataProvider\Authentication\TokenUserDataProvider;
use HuraTabory\DataProvider\Event\ViewEventDataProvider;
use HuraTabory\DataProvider\InitialStateDataProvider;
use HuraTabory\Http\HeadersFactory;
use Latte\Engine;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\HtmlResponse;

class ViewEventAction implements RequestHandlerInterface
{
    /** @var Engine */
    private $latte;

    /** @var InitialStateDataProvider */
    private $initialStateDataProvider;

    /** @var TokenUserDataProvider */
    private $tokenUserDataProvider;

    /** @var ViewEventDataProvider */
    private $viewEventDataProvider;

    /** @var HeadersFactory */
    private $headersFactory;

    public function __construct(
        Engine $latte,
        InitialStateDataProvider $initialStateDataProvider,
        TokenUserDataProvider $tokenUserDataProvider,
        ViewEventDataProvider $viewEventDataProvider,
        HeadersFactory $headersFactory
    ) {
        $this->latte = $latte;
        $this->initialStateDataProvider = $initialStateDataProvider;
        $this->tokenUserDataProvider = $tokenUserDataProvider;
        $this->viewEventDataProvider = $viewEventDataProvider;
        $this->headersFactory = $headersFactory;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $headers = $this->headersFactory->createDefault();

        $data = $this->initialStateDataProvider->getData($request);

        $path = $request->getUri()->getPath();
        $event = null;
        foreach ($data['events']['camps'] + $data['events']['trips'] as $camp) {
            if ($camp['url'] === $path) {
                $event = $camp;
                break;
            }
        }

        if ($event === null) {
            $event = $this->viewEventDataProvider->getData(
                $path,
                $this->tokenUserDataProvider->getUser($request),
                false
            );

            if ($event === null) {
                $event = 404;
            }

            $data['loadedEvents'][$path] = $event;
        }

        $html = $this->latte->renderToString(__DIR__ . '/view-event.latte', [
            'reactState' => $data,
            'event' => $event,
            'activeTab' => $request->getQueryParams()['tab'] ?? '',
            'metaDescription' => is_array($event) ? $event['description'] : null,
            'structuredData' => is_array($event) ? $this->createStructuredData($event, $data, $request->getUri()) : null,
        ]);

        return new HtmlResponse($html, 200, $headers->toArray());
    }

    private function createStructuredData(array $event, array $initialStateData, UriInterface $uri): array
    {
        $availability = 'https://schema.org/LimitedAvailability';
        if (!$event['hasOpenApplications']) {
            $availability = 'https://schema.org/Discontinued';
        } elseif (!$event['hasCapacity']) {
            $availability = 'https://schema.org/SoldOut';
        }

        return array_merge_recursive([
            '@context' => 'https://schema.org/',
            '@type' => 'Event',
            'url' => (string)$uri->withPath($event['url']),
            'name' => $event['name'],
            'startDate' => (new DateTimeImmutable($event['starts']))->format(DATE_RFC3339),
            'endDate' => (new DateTimeImmutable($event['ends']))->format(DATE_RFC3339),
            'description' => $event['description'],
            'eventStatus' => 'https://schema.org/EventScheduled',
            'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
            'image' => (string)$uri->withPath($event['banner']),
            'maximumAttendeeCapacity' => (string)$event['maxParticipants'],
            'typicalAgeRange' => str_replace(' let', '', $event['age']),
            'offers' => [
                '@type' => 'Offer',
                'url' => (string)$uri->withPath($event['url']),
                'priceCurrency' => 'CZK',
                'price' => (string)$event['price'],
                'availability' => $availability,
                'validThrough' => (new DateTimeImmutable($event['applicationsCloseAt']))->format(DATE_RFC3339),
            ],
            'organizer' => [
                'name' => $initialStateData['website']['name'],
                'url' => 'https://hura-tabory.cz',
                'email' => $initialStateData['website']['email'],
                'telephone' => $initialStateData['website']['phone'],
                'description' => $initialStateData['website']['description'],
                'legalName' => $initialStateData['website']['name'],
                'leiCode' => $initialStateData['website']['ico'],
                'slogan' => $initialStateData['website']['slogan'],
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => 'https://hura-tabory.cz/logo.png',
                    'height' => '60',
                    'width' => '159',
                ],
                'address' => [
                    '@type' => 'PostalAddress',
                    'postalCode' => '60200',
                    'streetAddress' => 'Nové sady 2',
                    'addressCountry' => 'CZ',
                    'addressRegion' => 'Brno',
                    'telephone' => $initialStateData['website']['phone'],
                ],
                'identifier' => [
                    '@type' => 'PropertyValue',
                    'name' => 'IČ',
                    'value' => $initialStateData['website']['ico'],
                ],
                'contactPoint' => [
                    '@type' => 'ContactPoint',
                    'name' => 'Patrik Vrtěna',
                    'contactType' => 'Hlavní vedoucí',
                    'telephone' => $initialStateData['website']['phone'],
                ],
            ],
            'location' => [
                'address' => [
                    'telephone' => $initialStateData['website']['phone'],
                ],
            ],
        ], $event['schema']);
    }
}
