<?php
declare(strict_types=1);

namespace HuraTabory\API\V1\UserProfile;


use DateTimeImmutable;
use HuraTabory\API\V1\Authentication\Exception\InvalidTokenException;
use HuraTabory\API\V1\Authentication\Service\TokenUser;
use HuraTabory\API\V1\UserProfile\Transformer\ReservationTransformer;
use HuraTabory\Http\HeadersFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use VCD2\Applications\ApplicationAddon;
use VCD2\Applications\Child as ApplicationChild;
use VCD2\Orm;
use VCD2\Users\Child;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\JsonResponse;

class ViewReservationAction implements RequestHandlerInterface
{
    /** @var TokenUser */
    private $tokenUser;

    /** @var Orm */
    private $orm;

    /** @var ReservationTransformer */
    private $reservationTransformer;

    /** @var HeadersFactory */
    private $headersFactory;

    public function __construct(
        TokenUser $tokenUser,
        Orm $orm,
        ReservationTransformer $reservationTransformer,
        HeadersFactory $headersFactory
    ) {
        $this->tokenUser = $tokenUser;
        $this->orm = $orm;
        $this->reservationTransformer = $reservationTransformer;
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

        $queryParams = $request->getQueryParams();
        if (!isset($queryParams['reservationId'])) {
            return new EmptyResponse(400, $headers->toArray());
        }

        $application = $this->orm->applications->getBy([
            'id' => (int)$queryParams['reservationId'],
            'user' => $user->id,
            'isApplied' => true,
        ]);
        if ($application === null) {
            return new EmptyResponse(404, $headers->toArray());
        }

        $data = $this->reservationTransformer->transform($application);

        return new JsonResponse($data, 200, $headers->toArray());
    }
}
