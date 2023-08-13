<?php
declare(strict_types=1);

namespace VCD2\Reviews;

use Hafo\Orm\Entity\Entity;
use VCD2\Users\User;

/**
 * @property int $id {primary}
 * @property User|null $user {m:1 User, oneSided=TRUE}
 * @property string|null $name
 * @property int $score
 * @property string $review
 * @property \DateTimeImmutable $addedAt {default now}
 */
class Review extends Entity
{
    public function __construct(?User $user, ?string $name, int $score, string $review)
    {
        parent::__construct();
        $this->user = $user;
        $this->name = $name;
        $this->score = $score;
        $this->review = $review;
    }
}
