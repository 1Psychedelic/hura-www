<?php
declare(strict_types=1);

namespace VCD2\Reviews\Service;

use Nextras\Orm\Collection\ICollection;
use VCD2\Orm;
use VCD2\Reviews\Review;
use VCD2\Users\User;

class Reviews
{
    /** @var Orm */
    private $orm;

    public function __construct(Orm $orm)
    {
        $this->orm = $orm;
    }

    public function canPostReview(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        if ($user->countEventsParticipated <= 0) {
            return false;
        }

        if ($this->orm->reviews->findBy(['user' => $user->id])->count() > 0) {
            return false;
        }

        return true;
    }

    public function postReview(User $user, int $score, string $reviewText): bool
    {
        if (!$this->canPostReview($user)) {
            return false;
        }

        $review = new Review($user, null, $score, $reviewText);
        $this->orm->reviews->persistAndFlush($review);

        return true;
    }

    /**
     * @return ICollection|Review[]
     */
    public function getReviews(): ICollection
    {
        return $this->orm->reviews->findAll()->orderBy('id', ICollection::DESC);
    }
}
