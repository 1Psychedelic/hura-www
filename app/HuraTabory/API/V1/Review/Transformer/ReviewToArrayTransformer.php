<?php
declare(strict_types=1);

namespace HuraTabory\API\V1\Review\Transformer;

use VCD2\Reviews\Review;

class ReviewToArrayTransformer
{
    public function transform(Review $review, int $styleId): array
    {
        return [
            'id' => $review->id,
            'styleId' => $styleId,
            'date' => strftime('%e. %B %Y', strtotime($review->addedAt->format('Y-m-d'))),
            'author' => [
                'name' => $review->user === null ? $review->name : $review->user->name,
                'avatar' => $review->user === null || $review->user->avatarSmall === null ? '/images/avatar.jpg' : $review->user->avatarSmall,
            ],
            'content' => $review->review,
        ];
    }
}
