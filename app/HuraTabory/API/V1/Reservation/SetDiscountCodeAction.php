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
use VCD2\Discounts\DiscountCodeException;
use VCD2\Discounts\Service\DiscountCodes;
use VCD2\Orm;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\JsonResponse;

class SetDiscountCodeAction implements RequestHandlerInterface
{
    /** @var TokenUser */
    private $tokenUser;

    /** @var Orm */
    private $orm;

    /** @var DiscountCodes */
    private $discountCodes;

    /** @var HeadersFactory */
    private $headersFactory;

    public function __construct(TokenUser $tokenUser, Orm $orm, DiscountCodes $discountCodes, HeadersFactory $headersFactory)
    {
        $this->tokenUser = $tokenUser;
        $this->orm = $orm;
        $this->discountCodes = $discountCodes;
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
        if (!isset($body['eventId'], $body['discountCode'])) {
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

        $draft = $this->orm->applications->getBy(
            [
                'isApplied' => false,
                'user' => $user->id,
                'event' => $body['eventId'],
            ]
        );

        if ($draft === null) {
            return new EmptyResponse(404, $headers->toArray());
        }

        $responseCode = 200;
        $response = [];

        $code = (string)$body['discountCode'];
        if(strlen($code) === 0) {
            if($draft->discountCode !== NULL) {
                $draft->resetDiscountCode();
                $response['message'] = 'Slevový kód byl odebrán z přihlášky.';
                $response['discountCode'] = '';
                $response['payingByDiscountCode'] = 0;
            }
        } else {
            $discount = $this->discountCodes->getUsableCodeForApplication($draft, $code);
            if($discount === NULL) {
                $draft->resetDiscountCode();
                $response['message'] = 'Zadaný slevový kód neexistuje nebo mu vypršela platnost.';
                $response['discountCode'] = '';
                $response['payingByDiscountCode'] = 0;
                $responseCode = 403;
            } else {
                try {
                    $draft->applyDiscountCode($discount);
                    $response['message'] = 'Slevový kód byl úspěšně aplikován.';
                    $response['discountCode'] = $discount->code;
                    $response['payingByDiscountCode'] = $discount->discount;
                } catch (DiscountCodeException $e) {
                    $response['message'] = 'Zadaný slevový kód neexistuje nebo mu vypršela platnost.';
                    $response['discountCode'] = '';
                    $response['payingByDiscountCode'] = 0;
                    $responseCode = 403;
                }
            }
        }

        $draft->refreshDiscount();
        $draft->recalculatePrice();
        $this->orm->persistAndFlush($draft);

        return new JsonResponse($response, $responseCode, $headers->toArray());
    }
}
