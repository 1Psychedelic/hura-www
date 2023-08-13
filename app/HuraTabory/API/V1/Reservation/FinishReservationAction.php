<?php
declare(strict_types=1);

namespace HuraTabory\API\V1\Reservation;


use HuraTabory\API\V1\Authentication\Exception\InvalidTokenException;
use HuraTabory\API\V1\Authentication\Service\TokenUser;
use HuraTabory\API\V1\Reservation\Service\ReservationService;
use HuraTabory\Http\HeadersFactory;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use VCD2\Applications\Service\Drafts;
use VCD2\Discounts\DiscountCodeException;
use VCD2\Discounts\DiscountException;
use VCD2\FlashMessageException;
use VCD2\Orm;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\JsonResponse;

class FinishReservationAction implements RequestHandlerInterface
{
    /** @var TokenUser */
    private $tokenUser;

    /** @var Orm */
    private $orm;

    /** @var ReservationService */
    private $reservationService;

    /** @var Drafts */
    private $drafts;

    /** @var Logger */
    private $logger;

    /** @var HeadersFactory */
    private $headersFactory;

    public function __construct(
        TokenUser $tokenUser,
        Orm $orm,
        ReservationService $reservationService,
        Drafts $drafts,
        Logger $logger,
        HeadersFactory $headersFactory
    ) {
        $this->tokenUser = $tokenUser;
        $this->orm = $orm;
        $this->reservationService = $reservationService;
        $this->drafts = $drafts;
        $this->logger = $logger->withName(get_class($this));
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
        if (!isset($body['eventId'], $body['parent'], $body['children'], $body['addons'], $body['notes'], $body['isPayingOnInvoice'])) {
            return new EmptyResponse(400, $headers->toArray());
        }

        $parentInfo = (array)$body['parent'];

        if (!isset($parentInfo['name'], $parentInfo['email'], $parentInfo['phone'], $parentInfo['street'], $parentInfo['city'], $parentInfo['zip'], $parentInfo['agreeGdpr'], $parentInfo['agreeTermsAndConditions'])) {
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

        $this->reservationService->upsertParentInfo($draft, $parentInfo);
        $this->reservationService->upsertChildren($draft, $body['children']);
        $this->reservationService->upsertAddons($draft, $body['addons']);

        $draft->notes = $body['notes'];
        $draft->isPayingOnInvoice = $body['isPayingOnInvoice'];

        $draft->refreshDiscount();
        $draft->recalculatePrice();
        $this->orm->persist($draft);
        $this->orm->flush();

        try {
            $this->drafts->finishDraft($draft);
        } catch (DiscountCodeException $e) {
            //$this->logger->notice(sprintf('Pokus o odeslání prihlášky %s selhal, protože vypršela platnost slevového kódu.', $this->draft));
            $draft->resetDiscountCode();
            $draft->refreshDiscount();
            $draft->recalculatePrice();

            $this->orm->persistAndFlush($draft);
            return new JsonResponse(['message' => 'Platnost Vašeho slevového kódu bohužel vypršela. Zkontrolujte si prosím novou cenu.'], 409);
        } catch (DiscountException $e) {
            //$this->logger->notice(sprintf('Pokus o odeslání přihlášky %s selhal, protože nastavená sleva už není platná.', $this->draft));
            return new JsonResponse(['message' => 'Bohužel skončila slevová akce ještě než jste stihli přihlášku odeslat. Zkontrolujte si prosím novou cenu.'], 409);
        } catch (FlashMessageException $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);

            return new JsonResponse([
                'message' => $e->getFlashMessage() !== null ? $e->getFlashMessage()->getMessage() : null,
                'exception' => get_class($e),
                'exceptionMessage' => $e->getMessage()], 409
            );
        }

        return new JsonResponse([
            'applicationId' => $draft->id,
        ], 201, $headers->toArray());
    }
}
