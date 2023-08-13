<?php
declare(strict_types=1);

namespace HuraTabory\API\V1\UserProfile;


use HuraTabory\API\V1\Authentication\Exception\InvalidTokenException;
use HuraTabory\API\V1\Authentication\Service\TokenUser;
use HuraTabory\API\V1\UserProfile\Transformer\ReservationTransformer;
use HuraTabory\Http\HeadersFactory;
use Nextras\Orm\Collection\ICollection;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use VCD2\Applications\Repository\ApplicationRepository;
use VCD2\Orm;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\JsonResponse;

class ListReservationsAction implements RequestHandlerInterface
{
    /** @var TokenUser */
    private $tokenUser;

    /** @var ReservationTransformer */
    private $reservationTransformer;

    /** @var HeadersFactory */
    private $headersFactory;

    public function __construct(
        TokenUser $tokenUser,
        ReservationTransformer $reservationTransformer,
        HeadersFactory $headersFactory
    ) {
        $this->tokenUser = $tokenUser;
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
        $page = (int)max(1, (int)($queryParams['page'] ?? 1));

        /*$userApplications = $this->orm->applications->findByUser($user->id)
            ->orderBy('isApplied', ICollection::ASC)
            ->orderBy('this->event->starts', ICollection::DESC)
            ->limitBy(5, ($page - 1) * 5);*/

        $userApplications = $user->applications->get()
            ->findBy(['isApplied' => true])
            ->resetOrderBy()
            ->orderBy('this->event->starts', ICollection::DESC)
            ->limitBy(5, ($page - 1) * 5);

        $data = [];
        foreach ($userApplications as $userApplication) {
            $data[$userApplication->id] = $this->reservationTransformer->transform($userApplication);
        }

        return new JsonResponse([
            'reservations' => $data,
        ], 200, $headers->toArray());
    }
}
