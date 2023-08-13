<?php
declare(strict_types=1);

namespace HuraTabory\API\V1\Reservation;


use HuraTabory\API\V1\Authentication\Exception\InvalidTokenException;
use HuraTabory\API\V1\Authentication\Service\TokenUser;
use HuraTabory\API\V1\Reservation\Service\ReservationService;
use HuraTabory\Http\HeadersFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use VCD2\Orm;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\JsonResponse;

class PayingOnInvoiceAction implements RequestHandlerInterface
{
    /** @var TokenUser */
    private $tokenUser;

    /** @var Orm */
    private $orm;

    /** @var HeadersFactory */
    private $headersFactory;

    public function __construct(
        TokenUser $tokenUser,
        Orm $orm,
        HeadersFactory $headersFactory
    ) {
        $this->tokenUser = $tokenUser;
        $this->orm = $orm;
        $this->headersFactory = $headersFactory;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $headers = $this->headersFactory->createDefault();

        try {
            $user = $this->tokenUser->getUser();
        } catch (InvalidTokenException $e) {
            return new EmptyResponse(403, $headers->toArray());
        }

        if ($user === null) {
            return new EmptyResponse(403, $headers->toArray());
        }

        $body = (array)json_decode((string)$request->getBody(), true);

        if (!isset($body['id'], $body['name'], $body['ico'], $body['dic'], $body['street'], $body['city'], $body['zip'])) {
            return new EmptyResponse(400, $headers->toArray());
        }

        $application = $this->orm->applications->get((int)$body['id']);

        if ($application === null || $application->user->id !== $user->id) {
            return new EmptyResponse(404, $headers->toArray());
        }

        if (!$application->isPayingOnInvoice || $application->hasFilledPayingOnInvoice) {
            return new EmptyResponse(403, $headers->toArray());
        }

        $application->setPayingOnInvoice(
            (string)$body['name'],
            (string)$body['ico'],
            (string)$body['dic'],
            (string)$body['city'],
            (string)$body['street'],
            (string)$body['zip'],
            $body['notes'] ?? null
        );
        $this->orm->persistAndFlush($application);

        return new EmptyResponse(200, $headers->toArray());
    }
}
