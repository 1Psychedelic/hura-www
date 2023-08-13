<?php
declare(strict_types=1);

namespace HuraTabory\API\V1\Authentication\Transformer;

use DateTimeImmutable;
use HuraTabory\API\V1\Authentication\Service\TokenUser;
use VCD2\Users\User;
use VCD2\Users\UserSession;

class UserToAuthenticationArrayTransformer
{
    /** @var UserSessionTransformer */
    private $userSessionTransformer;

    public function __construct(
        UserSessionTransformer $userSessionTransformer
    ) {
        $this->userSessionTransformer = $userSessionTransformer;
    }

    public function transform(?UserSession $userSession, string $jwt): array
    {
        if ($userSession === null) {
            return [
                'isLoggedIn' => false,
                'accessToken' => null,
                'userChildren' => [],
            ];
        }

        $user = $userSession->user;

        $userChildren = [];
        foreach ($user->children as $child) {
            $userChildren[] = [
                'childId' => $child->id,
                'name' => (string)$child->name,
                'dateBorn' => $child->dateBorn->format('Y-m-d'),
                'gender' => $child->gender,
                'adhd' => $child->adhd,
                'swimmer' => $child->swimmer,
                'firstTimer' => true,
                'health' => (string)$child->health,
            ];
        }

        return [
            'isLoggedIn' => true,
            'accessToken' => $jwt,
            'userProfile' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'street' => $user->street,
                'city' => $user->city,
                'zip' => $user->zip,
                'agreeGdpr' => $user->agreedPersonalData,
                'agreeTermsAndConditions' => $user->agreedTermsAndConditions,
                'avatar' => $user->avatarSmall ?? '/images/avatar.jpg',
                'remainingEventsForVip' => max(0, User::APPLICATIONS_FOR_VIP - $user->applicationsEligibleForVip->countStored()),
                'loginMethods' => [
                    'password' => $user->password !== null,
                    'facebook' => $user->facebookId !== null,
                    'google' => $user->googleId !== null,
                ],
            ],
            'userChildren' => $userChildren,
            'reservations' => (object)[],
            'reservationListPages' => (object)[],
            'reservationListLastPage' => 1,
            'userSessions' => $this->transformUserSessions($user, $userSession),
            'credits' => $this->transformCredits($user),
        ];
    }

    private function transformCredits(User $user): array
    {
        $now = new DateTimeImmutable();

        $credits = [];
        foreach ($user->credits as $credit) {
            if($credit->amount === 0 || ($credit->expiresAt !== NULL && $credit->expiresAt < $now) || $credit->expiresAt === null) {
                continue;
            }
            $key = $credit->expiresAt === NULL ? NULL : $credit->expiresAt->format('j.n.Y');
            if(!array_key_exists($key, $credits)) {
                $credits[$key] = 0;
            }
            $credits[$key] += $credit->amount;
        }

        $expirations = [];
        foreach ($credits as $expiration => $amount) {
            $expirations[] = [
                'amount' => $amount,
                'expiration' => $expiration,
            ];
        }

        return [
            'total' => $user->creditBalance,
            'expirations' => $expirations,
        ];
    }

    private function transformUserSessions(User $user, UserSession $currentSession): array
    {
        $userSessions = [];

        foreach ($user->sessions->get()->findBy(['enabled' => true]) as $userSession) {
            /** @var UserSession $userSession */
            $userSessions[] = $this->userSessionTransformer->transform($userSession, $currentSession);
        }

        return $userSessions;
    }
}
