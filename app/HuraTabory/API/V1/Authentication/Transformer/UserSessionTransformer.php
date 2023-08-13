<?php
declare(strict_types=1);

namespace HuraTabory\API\V1\Authentication\Transformer;

use DateTimeImmutable;
use VCD2\Users\UserSession;

class UserSessionTransformer
{
    public function transform(UserSession $userSession, UserSession $currentSession): array
    {
        $now = (new DateTimeImmutable())->getTimestamp();

        return [
            'id' => $userSession->id,
            'isCurrent' => $userSession->id === $currentSession->id,
            'isActive' => abs($userSession->lastSeen->getTimestamp() - $now) <= 15 * 60,
            'createdAt' => $userSession->createdAt->format('c'),
            'lastSeen' => $userSession->lastSeen->format('c'),
            'ip' => $userSession->ip,
            'deviceDescription' => $userSession->deviceDescription,
        ];
    }
}
