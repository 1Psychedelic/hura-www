<?php
declare(strict_types=1);

namespace Hafo\Http;

use DateTimeImmutable;

class CacheHeaders
{
    /** @var string|null */
    private $etag;

    /** @var DateTimeImmutable|null */
    private $lastModified;

    public function __construct(?string $etag = null, ?DateTimeImmutable $lastModified = null)
    {
        $this->etag = $etag;
        $this->lastModified = $lastModified;
    }

    public function getEtag(): ?string
    {
        return $this->etag;
    }

    public function getLastModified(): ?DateTimeImmutable
    {
        return $this->lastModified;
    }
}