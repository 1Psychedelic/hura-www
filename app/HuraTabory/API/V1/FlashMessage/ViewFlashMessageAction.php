<?php
declare(strict_types=1);

namespace HuraTabory\API\FlashMessage;

use HuraTabory\Domain\FlashMessage\FlashMessageRepository;
use HuraTabory\Http\HeadersFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\JsonResponse;

class ViewFlashMessageAction implements RequestHandlerInterface
{
    /** @var FlashMessageRepository */
    private $flashMessageRepository;

    /** @var HeadersFactory */
    private $headersFactory;

    public function __construct(FlashMessageRepository $flashMessageRepository, HeadersFactory $headersFactory)
    {
        $this->flashMessageRepository = $flashMessageRepository;
        $this->headersFactory = $headersFactory;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $headers = $this->headersFactory->createDefault();

        $hash = (string)($request->getQueryParams()['hash'] ?? '');
        if ($hash === '') {
            return new EmptyResponse(404, $headers->toArray());
        }

        $flashMessage = $this->flashMessageRepository->find($hash);
        if ($flashMessage === null) {
            return new EmptyResponse(404, $headers->toArray());
        }

        return new JsonResponse([
            'type' => $flashMessage->getType(),
            'message' => $flashMessage->getMessage(),
        ], 200, $headers->toArray());
    }
}
