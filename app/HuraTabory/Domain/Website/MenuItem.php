<?php
declare(strict_types=1);

namespace HuraTabory\Domain\Website;

class MenuItem
{
    /** @var int */
    private $id;

    /** @var string */
    private $url;

    /** @var string */
    private $text;

    /** @var bool */
    private $isExternal;

    public function __construct(int $id, string $url, string $text, bool $isExternal)
    {
        $this->id = $id;
        $this->url = $url;
        $this->text = $text;
        $this->isExternal = $isExternal;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function isExternal(): bool
    {
        return $this->isExternal;
    }
}
