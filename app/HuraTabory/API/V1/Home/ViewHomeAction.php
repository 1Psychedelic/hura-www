<?php
declare(strict_types=1);

namespace HuraTabory\API\V1\Home;

use HuraTabory\API\V1\Authentication\Exception\InvalidTokenException;
use HuraTabory\DataProvider\Home\ViewHomeDataProvider;
use HuraTabory\Domain\Homepage\HomepageConfig;
use HuraTabory\Domain\Homepage\HomepageRepository;
use HuraTabory\Domain\Website\WebsiteRepository;
use HuraTabory\Http\HeadersFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use HuraTabory\API\V1\Authentication\Service\TokenUser;
use HuraTabory\API\V1\Authentication\Transformer\UserToAuthenticationArrayTransformer;
use HuraTabory\API\V1\Event\Transformer\EventDetailTransformer;
use HuraTabory\API\V1\Review\Transformer\ReviewToArrayTransformer;
use VCD2\Events\Event;
use VCD2\Orm;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\JsonResponse;

class ViewHomeAction implements RequestHandlerInterface
{
    /** @var ViewHomeDataProvider */
    private $viewHomeDataProvider;

    /** @var TokenUser */
    private $tokenUser;

    /** @var HeadersFactory */
    private $headersFactory;

    public function __construct(
        ViewHomeDataProvider $viewHomeDataProvider,
        TokenUser $tokenUser,
        HeadersFactory $headersFactory
    ) {
        $this->viewHomeDataProvider = $viewHomeDataProvider;
        $this->tokenUser = $tokenUser;
        $this->headersFactory = $headersFactory;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /*$eventsCacheHeaders = $this->orm->events->getUpcomingCacheHeaders();

        $requestEtag = $request->getHeaderLine('If-None-Match');
        $serverEtag = null;*/

        $headers = $this->headersFactory->createDefault();
        /*if ($eventsCacheHeaders !== null) {
            try {
                $user = $this->tokenUser->getUser();
            } catch (InvalidTokenException $e) {
                return new EmptyResponse(401, $headers);
            }

            $serverEtag = md5(
                $eventsCacheHeaders->getEtag()
                . ($user === null ? '0' : (string)$user->vipLevel)
                . $homepageConfig->getCacheHeaders()->getEtag()
                . $websiteConfig->getCacheHeaders()->getEtag()
            );
            $headers['ETag'] = $serverEtag;

            if ($requestEtag === $serverEtag) {
                return new EmptyResponse(304, $headers);
            }
        }*/

        try {
            $user = $this->tokenUser->getUser();
        } catch (InvalidTokenException $e) {
            return new EmptyResponse(401, $headers->toArray());
        }

        $data = $this->viewHomeDataProvider->getData($user);

        return new JsonResponse($data, 200, $headers->toArray());
    }
}
