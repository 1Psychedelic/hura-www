<?php
declare(strict_types=1);

namespace VCD2\FacebookImages\DTO;

class FacebookImage
{
    /** @var string */
    private $image;

    /** @var int */
    private $width;

    /** @var int */
    private $height;

    public function __construct(string $image, int $width, int $height)
    {
        $this->image = $image;
        $this->width = $width;
        $this->height = $height;
    }

    public function getImage(): string
    {
        return $this->image;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }
}
