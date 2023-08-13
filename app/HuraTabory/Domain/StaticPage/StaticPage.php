<?php
declare(strict_types=1);

namespace HuraTabory\Domain\StaticPage;

class StaticPage
{
    /** @var string */
    private $slug;

    /** @var string */
    private $name;

    /** @var string|null */
    private $keywords;

    /** @var string|null */
    private $content;

    public function __construct(string $slug, string $name, ?string $keywords, ?string $content)
    {
        $this->slug = $slug;
        $this->name = $name;
        $this->keywords = $keywords;
        $this->content = $content;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getKeywords(): ?string
    {
        return $this->keywords;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }
}
