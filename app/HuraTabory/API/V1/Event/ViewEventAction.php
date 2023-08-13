<?php
declare(strict_types=1);

namespace HuraTabory\API\V1\Event;

use HuraTabory\API\V1\Authentication\Exception\InvalidTokenException;
use HuraTabory\DataProvider\Event\ViewEventDataProvider;
use HuraTabory\Http\HeadersFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use HuraTabory\API\V1\Authentication\Service\TokenUser;
use HuraTabory\API\V1\Event\Transformer\EventDetailTransformer;
use VCD2\Events\Event;
use VCD2\Orm;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\JsonResponse;

class ViewEventAction implements RequestHandlerInterface
{
    /** @var ViewEventDataProvider */
    private $viewEventDataProvider;

    /** @var TokenUser */
    private $tokenUser;

    /** @var HeadersFactory */
    private $headersFactory;

    public function __construct(
        ViewEventDataProvider $viewEventDataProvider,
        TokenUser $tokenUser,
        HeadersFactory $headersFactory
    ) {
        $this->viewEventDataProvider = $viewEventDataProvider;
        $this->tokenUser = $tokenUser;
        $this->headersFactory = $headersFactory;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $headers = $this->headersFactory->createDefault();

        $queryParams = $request->getQueryParams();
        if (!isset($queryParams['path'])) {
            return new EmptyResponse(400, $headers->toArray());
        }

        $path = (string)$queryParams['path'];

        try {
            $tokenUser = $this->tokenUser->getUser();
        } catch (InvalidTokenException $e) {
            return new EmptyResponse(401, $headers->toArray());
        }


        /*$requestEtag = $request->getHeaderLine('If-None-Match');

        $serverEtag = md5(
            $event->updatedAt->format('Y-m-d H:i:s') . '_'
            . ($event->currentPriceLevel->isPersisted() ? $event->currentPriceLevel->id : '') . '_'
            . ($event->currentDiscount === null ? '' : $event->currentDiscount->id) . '_'
            . ($user === null ? '0' : (string)$user->vipLevel)
        );

        $headers['ETag'] = $serverEtag;

        if ($requestEtag === $serverEtag) {
            return new EmptyResponse(304, $headers);
        }*/

        $data = $this->viewEventDataProvider->getData($path, $tokenUser);

        return new JsonResponse($data, 200, $headers->toArray());
    }
}
