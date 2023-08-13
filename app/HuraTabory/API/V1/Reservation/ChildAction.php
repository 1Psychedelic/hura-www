<?php
declare(strict_types=1);

namespace HuraTabory\API\V1\Reservation;


use DateTimeImmutable;
use HuraTabory\API\V1\Authentication\Exception\InvalidTokenException;
use HuraTabory\API\V1\Authentication\Service\TokenUser;
use HuraTabory\Http\HeadersFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use VCD2\Applications\Child as ApplicationChild;
use VCD2\Orm;
use VCD2\Users\Child;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\JsonResponse;

class ChildAction implements RequestHandlerInterface
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
        $body['notes'] = null; // todo temp fix
        if (!isset($body['eventId'], $body['childId'], $body['email'], $body['name'], $body['gender'], $body['adhd'], $body['dateBorn'], $body['swimmer'], $body['firstTimer'], $body['health'])) {
            return new EmptyResponse(400, $headers->toArray());
        }

        $event = $this->orm->events->get((int)$body['eventId']);
        if ($event === null || !$event->visible) {
            return new EmptyResponse(404, $headers->toArray());
        }

        if (!$event->hasOpenApplications) {
            return new JsonResponse([
                'message' => 'Je nám líto, přihlášky na tuto akci jsou již bohužel uzavřené.',
            ], 403, $headers->toArray());
        }

        $draft = $this->orm->applications->getBy([
            'isApplied' => false,
            'user' => $user->id,
            'event' => $body['eventId'],
        ]);

        if ($draft === null) {
            return new EmptyResponse(404, $headers->toArray());
        }

        $child = null;
        if (is_int($body['childId'])) {
            $child = $this->orm->children->get($body['childId']);
            if (!$child->parents->has($user)) {
                $child = null;
            }
        }

        if ($child === null) {
            $child = Child::createFromArray($user, $body);
            $this->orm->persistAndFlush($child);
        } else {
            $child->updateInfo(
                $body['name'],
                $body['gender'],
                new DateTimeImmutable($body['dateBorn']),
                $body['swimmer'],
                $body['adhd'],
                $body['health'],
                null,
                null
            );
            $this->orm->persist($child);
        }

        $applicationChild = $this->orm->applicationChildren->getBy([
            'application' => $draft->id,
            'child' => $child->id,
        ]);

        if ($applicationChild === null) {
            $applicationChild = ApplicationChild::createFromArray($draft, $child, $body);
        } else {
            $applicationChild->updateInfo(
                $body['name'],
                $body['gender'],
                new DateTimeImmutable($body['dateBorn']),
                $body['swimmer'],
                $body['adhd'],
                $body['health'],
                null,
                null
            );
        }
        $this->orm->persist($applicationChild);

        $draft->refreshDiscount();
        $draft->recalculatePrice();
        $this->orm->persist($draft);

        $this->orm->flush();

        return new JsonResponse([
            'childId' => $child->id,
            'applicationChildId' => $applicationChild->id,
        ], 201, $headers->toArray());
    }
}
