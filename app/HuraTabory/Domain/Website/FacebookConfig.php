<?php
declare(strict_types=1);

namespace HuraTabory\Domain\Website;

class FacebookConfig
{
    /** @var string */
    private $appId;

    public function getAppId(): string
    {
        return $this->appId;
    }

    public function setAppId(string $appId): void
    {
        $this->appId = $appId;
    }
}
