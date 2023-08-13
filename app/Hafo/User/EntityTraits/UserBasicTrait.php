<?php

namespace Hafo\User\EntityTraits;

/**
 * @property int $id {primary}
 *
 * @property string $name
 *
 * @property string|NULL $email
 * @property bool $emailVerified {default FALSE}
 * @property string|NULL $emailVerifyHash
 *
 * @property string|NULL $loginToken
 * @property string|NULL $password
 * @property string|NULL $passwordRestore
 * @property \DateTimeImmutable|NULL $passwordRestoreRequestedAt
 *
 * @property \DateTimeImmutable $registeredAt {default now}
 * @property \DateTimeImmutable|NULL $lastLogin
 * @property \DateTimeImmutable|NULL $lastActive
 *
 * @property string $ip
 * @property string $host
 */
trait UserBasicTrait {



}
