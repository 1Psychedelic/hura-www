<?php
declare(strict_types=1);

namespace VCD2\Events;

class EventPriceInfo
{
    /** @var int */
    private $basePrice;

    /** @var int|null */
    private $discountPrice;

    /** @var \DateTimeImmutable|null */
    private $discountedUntil;

    public function __construct(int $basePrice, ?int $discountPrice, ?\DateTimeImmutable $discountedUntil)
    {
        $this->basePrice = $basePrice;
        $this->discountPrice = $discountPrice !== null && $discountPrice < $basePrice ? $discountPrice : null;
        $this->discountedUntil = $this->discountPrice !== null ? $discountedUntil : null;
    }

    public function getBasePrice(): int
    {
        return $this->basePrice;
    }

    public function getDiscountPrice(): ?int
    {
        return $this->discountPrice;
    }

    public function getDiscountedUntil(): ?\DateTimeImmutable
    {
        return $this->discountedUntil;
    }
}
