<?php
declare(strict_types=1);

namespace HuraTabory\Domain\Game;

class Game
{
    /** @var int */
    private $id;

    /** @var string */
    private $name;

    /** @var string */
    private $slug;

    /** @var bool */
    private $visibleOnHomepage;

    /** @var string|null */
    private $bannerSmall;

    /** @var string|null */
    private $bannerLarge;

    /** @var string */
    private $descriptionShort;

    /** @var string */
    private $descriptionLong;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    public function isVisibleOnHomepage(): bool
    {
        return $this->visibleOnHomepage;
    }

    public function setVisibleOnHomepage(bool $visibleOnHomepage): void
    {
        $this->visibleOnHomepage = $visibleOnHomepage;
    }

    public function getBannerSmall(): ?string
    {
        return $this->bannerSmall;
    }

    public function setBannerSmall(?string $bannerSmall): void
    {
        $this->bannerSmall = $bannerSmall;
    }

    public function getBannerLarge(): ?string
    {
        return $this->bannerLarge;
    }

    public function setBannerLarge(?string $bannerLarge): void
    {
        $this->bannerLarge = $bannerLarge;
    }

    public function getDescriptionShort(): string
    {
        return $this->descriptionShort;
    }

    public function setDescriptionShort(string $descriptionShort): void
    {
        $this->descriptionShort = $descriptionShort;
    }

    public function getDescriptionLong(): string
    {
        return $this->descriptionLong;
    }

    public function setDescriptionLong(string $descriptionLong): void
    {
        $this->descriptionLong = $descriptionLong;
    }
}
