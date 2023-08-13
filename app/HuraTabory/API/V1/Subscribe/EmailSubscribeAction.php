<?php
declare(strict_types=1);

namespace HuraTabory\API\Subscribe;

use HuraTabory\Http\HeadersFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use VCD\Users\Newsletter;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\JsonResponse;

class EmailSubscribeAction implements RequestHandlerInterface
{
    /** @var HeadersFactory */
    private $headersFactory;

    /** @var Newsletter */
    private $newsletter;

    public function __construct(HeadersFactory $headersFactory, Newsletter $newsletter)
    {
        $this->headersFactory = $headersFactory;
        $this->newsletter = $newsletter;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $headers = $this->headersFactory->createDefault();

        $body = (array)json_decode((string)$request->getBody(), true);
        if (!isset($body['email'])) {
            return new EmptyResponse(400, $headers->toArray());
        }

        if ($this->newsletter->isAdded((string)$body['email'])) {
            return new JsonResponse(['error' => 'E-mail už je přihlášen k odběru novinek.'], 403, $headers->toArray());
        }

        $this->newsletter->add((string)$body['email']);

        return new EmptyResponse(201, $headers->toArray());
    }
}
