<?php
declare(strict_types=1);

namespace HuraTabory\API\V1\Reservation;


use HuraTabory\API\V1\Authentication\Exception\InvalidTokenException;
use HuraTabory\API\V1\Authentication\Service\TokenUser;
use HuraTabory\Http\HeadersFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use VCD2\Applications\Child as ApplicationChild;
use VCD2\Orm;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\JsonResponse;

class ChildRemoveFromReservationAction implements RequestHandlerInterface
{
    /** @var TokenUser */
    private $tokenUser;

    /** @var Orm */
    private $orm;

    /** @var HeadersFactory */
    private $headersFactory;

    public function __construct(TokenUser $tokenUser, Orm $orm, HeadersFactory $headersFactory)
    {
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
        if (!isset($body['eventId'], $body['childId'])) {
            return new EmptyResponse(400, $headers->toArray());
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

        $child = $this->orm->applicationChildren->getBy([
            'application' => $draft->id,
            'child' => $body['childId'],
        ]);
        if ($child !== null) {
            $this->orm->applicationChildren->remove($child);
        }

        $draft->refreshDiscount();
        $draft->recalculatePrice();
        $this->orm->persistAndFlush($draft);

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
