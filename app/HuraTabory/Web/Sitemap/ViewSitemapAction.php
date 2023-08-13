<?php
declare(strict_types=1);

namespace HuraTabory\Web\Sitemap;

use HuraTabory\Http\HeadersFactory;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use VCD2\Events\Event;
use VCD2\Orm;
use Zend\Diactoros\Response\XmlResponse;

class ViewSitemapAction implements RequestHandlerInterface
{
    /** @var Orm */
    private $orm;

    /** @var Cache */
    private $cache;

    /** @var HeadersFactory */
    private $headersFactory;

    public function __construct(Orm $orm, IStorage $storage, HeadersFactory $headersFactory)
    {
        $this->orm = $orm;
        $this->cache = new Cache($storage, 'sitemap');
        $this->headersFactory = $headersFactory;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $xml = $this->cache->load('sitemap', function (&$dependencies) use ($request) {
            $dependencies[Cache::EXPIRE] = '+12 hours';

            $builtInUrls = [
                '',
                '/tabory',
                '/vylety',
            ];

            $eventTypeUrl = [
                Event::TYPE_CAMP => '/tabor/',
                Event::TYPE_CAMP_SPRING => '/tabor/',
                Event::TYPE_TRIP => '/vylet/',
            ];

            $uri = $request->getUri()->withQuery('')->withPath('')->withFragment('');

            $urls = [];
            foreach ($builtInUrls as $builtInUrl) {
                $urls[] = $uri . $builtInUrl;
            }

            foreach ($this->orm->events->findBy(['visible' => true]) as $event) {
                $urls[] = $uri . $eventTypeUrl[$event->type] . $event->slug;
                $isFirst = true;
                foreach ($event->tabs as $tab) {
                    if ($isFirst) {
                        $isFirst = false;
                        continue;
                    }
                    $urls[] = $uri . $eventTypeUrl[$event->type] . $event->slug . '/' . $tab->slug;
                }
            }

            $xml = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

            foreach ($urls as $url) {
                $xml .= '<url><loc>' . $url . '</loc></url>';
            }

            $xml .= '</urlset>';

            return $xml;
        });

        return new XmlResponse($xml, 200, $this->headersFactory->createDefault()->toArray());
    }
}
