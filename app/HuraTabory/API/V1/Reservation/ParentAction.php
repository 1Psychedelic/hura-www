<?php
declare(strict_types=1);

namespace HuraTabory\API\V1\Reservation;

use Hafo\DI\Container;
use Hafo\Security\SecurityException;
use HuraTabory\API\V1\Authentication\Exception\InvalidTokenException;
use HuraTabory\API\V1\Reservation\Service\ReservationService;
use HuraTabory\Http\HeadersFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use HuraTabory\API\V1\Authentication\Service\JwtService;
use HuraTabory\API\V1\Authentication\Service\TokenUser;
use HuraTabory\API\V1\Authentication\Transformer\UserToAuthenticationArrayTransformer;
use VCD2\Emails\Service\Emails\AccountCreatedMail;
use VCD2\Orm;
use VCD2\Users\Service\AutomaticSignup;
use VCD2\Users\Service\UserSessions;
use VCD2\Users\User;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\JsonResponse;

class ParentAction implements RequestHandlerInterface
{
    /** @var TokenUser */
    private $tokenUser;

    /** @var Orm */
    private $orm;

    /** @var AccountCreatedMail */
    private $accountCreatedMail;

    /** @var AutomaticSignup */
    private $automaticSignup;

    /** @var UserSessions */
    private $userSessions;

    /** @var JwtService */
    private $jwtService;

    /** @var UserToAuthenticationArrayTransformer */
    private $userToAuthenticationArrayTransformer;

    /** @var ReservationService */
    private $reservationService;

    /** @var HeadersFactory */
    private $headersFactory;

    public function __construct(
        TokenUser $tokenUser,
        Orm $orm,
        AccountCreatedMail $accountCreatedMail,
        AutomaticSignup $automaticSignup,
        UserSessions $userSessions,
        JwtService $jwtService,
        UserToAuthenticationArrayTransformer $userToAuthenticationArrayTransformer,
        ReservationService $reservationService,
        HeadersFactory $headersFactory
    ) {
        $this->tokenUser = $tokenUser;
        $this->orm = $orm;
        $this->accountCreatedMail = $accountCreatedMail;
        $this->automaticSignup = $automaticSignup;
        $this->userSessions = $userSessions;
        $this->jwtService = $jwtService;
        $this->userToAuthenticationArrayTransformer = $userToAuthenticationArrayTransformer;
        $this->reservationService = $reservationService;
        $this->headersFactory = $headersFactory;
    }


    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $headers = $this->headersFactory->createDefault();

        $body = (array)json_decode((string)$request->getBody(), true);

        if (!isset($body['eventId'], $body['name'], $body['email'], $body['phone'], $body['street'], $body['city'], $body['zip'], $body['agreeGdpr'], $body['agreeTermsAndConditions'])) {
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

        try {
            $userSession = $this->tokenUser->getUserSession();
            $user = $this->tokenUser->getUser();
        } catch (InvalidTokenException $e) {
            return new EmptyResponse(401, $headers->toArray());
        }

        $headers = $this->headersFactory->createDefault();

        if ($user === null || $userSession === null) {
            try {
                $user = $this->automaticSignup($body['email'], $body['name']);
                $userSession = $this->userSessions->createSession($user, $request);

                $refreshToken = $this->jwtService->buildRefreshToken($user->id, $userSession->id);
                $headers = $headers->withCookie($this->headersFactory->getRefreshTokenCookieTemplate(), $refreshToken);
            } catch (SecurityException $e) {
                return new JsonResponse([
                    'message' => $e->getMessage(),
                ], 401, $headers->toArray());
            } catch (InvalidTokenException $e) {
                return new EmptyResponse(401, $headers->toArray());
            }
        }

        $draft = $this->orm->applications->getBy([
            'isApplied' => false,
            'user' => $user->id,
            'event' => $body['eventId'],
        ]);

        if ($draft === null) {
            $draft = $event->createApplication($user);
        }

        $this->reservationService->upsertParentInfo($draft, $body, true);

        $jwt = $this->jwtService->buildJwt($user->id, $userSession->id);
        $data = $this->userToAuthenticationArrayTransformer->transform($userSession, $jwt);

        return new JsonResponse([
            'authentication' => $data,
        ], 201, $headers->toArray());
    }

    /**
     * @param string $email
     * @param string $name
     * @return User
     * @throws SecurityException
     */
    private function automaticSignup(string $email, string $name): User
    {
        $tokenUser = $this->tokenUser->getUser();
        if($tokenUser === NULL) {
            $existingUser = $this->orm->users->getByEmail($email);
            if($existingUser !== NULL) {
                if($existingUser->canLogin) {
                    throw new SecurityException('Zadaný e-mail je již registrován. Pro pokračování pod tímto e-mailem se prosím přihlašte.');
                }
                $this->accountCreatedMail->send($email);

                throw new SecurityException('Tento e-mail je již registrován, ale účet není aktivovaný. Instrukce pro aktivaci účtu najdete ve své e-mailové schránce. V případě potíží nás neváhejte kontaktovat.');
            }

            try {
                $this->automaticSignup->check($email);
                $this->automaticSignup->onSignup[] = function(User $u) {
                    //$this->apiUser = $u;
                    //$this->container->get(IdAuthenticator::class)->login($u->id);
                    $this->accountCreatedMail->send($u->email);

                    //$jwt = $this->buildJwt($u->id);

                    //$this->payload->authentication = $this->buildAuthenticationObject($jwt, $u);
                };

                return $this->automaticSignup->signup($email, $name);
            } catch (SecurityException $e) {
                //$hash = $this->container->get(Emails::class)->requestEmailVerifyHash($email);
                //$this->container->get(ApplicationVerifyMail::class)->send($email, $hash, $this->draft->event->id);
                $this->accountCreatedMail->send($email);

                throw new SecurityException('Tento e-mail je již registrován, ale účet není aktivovaný. Instrukce pro aktivaci účtu najdete ve své e-mailové schránce. V případě potíží nás neváhejte kontaktovat.');
            }
        }

        return $tokenUser;
    }
}
