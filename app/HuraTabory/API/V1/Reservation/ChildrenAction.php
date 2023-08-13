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

class ChildrenAction implements RequestHandlerInterface
{
    /** @var TokenUser */
    private $tokenUser;

    /** @var Orm */
    private $orm;

    /** @var ReservationService */
    private $reservationService;

    /** @var HeadersFactory */
    private $headersFactory;

    public function __construct(
        TokenUser $tokenUser,
        Orm $orm,
        ReservationService $reservationService,
        HeadersFactory $headersFactory
    ) {
        $this->tokenUser = $tokenUser;
        $this->orm = $orm;
        $this->reservationService = $reservationService;
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
        if (!isset($body['eventId'], $body['children'])) {
            return new EmptyResponse(400, $headers->toArray());
        }

        if (!is_array($body['children'])) {
            return new EmptyResponse(400, $headers->toArray());
        }

        foreach ($body['children'] as $childData) {
            if (!isset($childData['childId'], $childData['name'], $childData['gender'], $childData['adhd'], $childData['dateBorn'], $childData['swimmer'], $childData['firstTimer'], $childData['health'])) {
                return new EmptyResponse(400, $headers->toArray());
            }
        }

        $event = $this->orm->events->get((int)$body['eventId']);
        if ($event === null || !$event->visible) {
            return new EmptyResponse(404, $headers->toArray());
        }

        if (!$event->hasOpenApplications) {
            return new JsonResponse([
                'message' => 'Je nám líto, přihlášky na tuto akci jsou již bohužel uzavřené.',
            ], 403);
        }

        $draft = $this->orm->applications->getBy([
            'isApplied' => false,
            'user' => $user->id,
            'event' => $body['eventId'],
        ]);

        if ($draft === null) {
            return new EmptyResponse(404, $headers->toArray());
        }

        $this->reservationService->upsertChildren($draft, $body['children'], true);

        $childIdsInApplication = [];
        foreach ($draft->children as $child) {
            $childIdsInApplication[$child->child->id] = $child->child->id;
        }

        $children = [];
        foreach ($user->children as $child) {
            $children[] = [
                'childId' => $child->id,
                'name' => (string)$child->name,
                'dateBorn' => $child->dateBorn->format('Y-m-d'),
                'gender' => $child->gender,
                'adhd' => $child->adhd,
                'swimmer' => $child->swimmer,
                'firstTimer' => true,
                'health' => (string)$child->health,
                'isInReservation' => isset($childIdsInApplication[$child->id]),
            ];
        }

        return new JsonResponse([
            'children' => $children,
        ], 200, $headers->toArray());
    }
}
