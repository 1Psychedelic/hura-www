<?php
declare(strict_types=1);

namespace VCD2\Firebase;

use Nextras\Orm\Relationships\ManyHasOne;
use VCD2\Entity;
use VCD2\Users\User;

/**
 * @property int $id {primary}
 * @property ManyHasOne|User $user {m:1 User::$firebasePushTokens}
 * @property string $token
 */
class FirebasePushToken extends Entity
{
    public function __construct(User $user, string $token)
    {
        parent::__construct();
        $this->user = $user;
        $this->token = $token;
    }
}
