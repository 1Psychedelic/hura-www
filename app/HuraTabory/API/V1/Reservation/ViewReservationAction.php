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
            return new EmptyResponse(204, $headers->toArray());
        }

        $queryParams = $request->getQueryParams();
        if (!isset($queryParams['eventId'])) {
            return new EmptyResponse(400, $headers->toArray());
        }

        $event = $this->orm->events->get((int)$queryParams['eventId']);
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
            'event' => $queryParams['eventId'],
        ]);

        if ($draft === null) {
            return new EmptyResponse(204, $headers->toArray());
        }

        $childrenInApplication = [];
        foreach ($draft->children as $applicationChild) {
            $childrenInApplication[$applicationChild->child->id] = $applicationChild->child->id;
        }

        $data = [
            'parent' => [
                'name' => $draft->name,
                'phone' => $draft->phone,
                'email' => $draft->email,
                'street' => $draft->street,
                'city' => $draft->city,
                'zip' => $draft->zip,
                'agreeGdpr' => $draft->agreedPersonalData,
                'agreeTermsAndConditions' => $draft->agreedTermsAndConditions,
            ],
            'children' => [],
            'addons' => [],
            'discounts' => [
                'canPayByCredit' => $draft->canPayByCredit,
                'canUseDiscountCode' => $draft->canUseDiscountCode,
                'payingByCredit' => $draft->canPayByCredit && $draft->isPayingByCredit ? $user->creditBalance : 0,
                'payingByDiscountCode' => !$draft->canUseDiscountCode || $draft->discountCode === null ? 0 : $draft->discountCode->discount,
                'discountCode' => !$draft->canUseDiscountCode || $draft->discountCode === null ? '' : $draft->discountCode->code,
            ],
        ];

        foreach ($user->children as $child) {
            $data['children'][] = [
                'childId' => $child->id,
                'name' => $child->name,
                'gender' => $child->gender,
                'adhd' => $child->adhd,
                'dateBorn' => $child->dateBorn->format('Y-m-d'),
                'swimmer' => $child->swimmer,
                'firstTimer' => true,
                'health' => $child->health,
                'isInReservation' => isset($childrenInApplication[$child->id]),
            ];
        }

        foreach ($event->addons as $addon) {
            if (!$addon->enabled) {
                continue;
            }
            /** @var ApplicationAddon|null $draftAddon */
            $draftAddon = $draft->addons->get()->getBy(['addon' => $addon->id]);
            $data['addons'][$addon->id] = $draftAddon === null ? 0 : $draftAddon->amount;
        }

        return new JsonResponse($data, 200, $headers->toArray());
    }
}
