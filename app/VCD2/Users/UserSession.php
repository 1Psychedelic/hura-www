<?php
declare(strict_types=1);

namespace VCD2\Users;


use DateTimeImmutable;
use Nextras\Orm\Relationships\ManyHasOne;
use VCD2\Entity;

/**
 * @property int $id {primary}
 *
 * @property ManyHasOne|User $user {m:1 User::$sessions}
 * @property bool $enabled {default TRUE}
 * @property DateTimeImmutable $createdAt {default now}
 * @property DateTimeImmutable $lastSeen {default now}
 * @property string $ip
 * @property string $deviceDescription
 *
 */
class UserSession extends Entity
{
}
