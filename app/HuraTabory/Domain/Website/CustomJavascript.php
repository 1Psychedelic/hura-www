<?php
declare(strict_types=1);

namespace HuraTabory\Domain\Website;

use VCD2\Users\User;

class CustomJavascript
{
    public const VISIBILITY_DISABLED_FOR_ALL = 0;
    public const VISIBILITY_ENABLED_FOR_ALL = 1;
    public const VISIBILITY_ENABLED_FOR_ALL_BUT_ADMIN = 2;
    public const VISIBILITY_ENABLED_FOR_GUESTS_ONLY = 3;

    /** @var int */
    private $id;

    /** @var string */
    private $code;

    /** @var int */
    private $visibility;

    public function __construct(int $id, string $code, int $visibility)
    {
        $this->id = $id;
        $this->code = $code;
        $this->visibility = $visibility;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getVisibility(): int
    {
        return $this->visibility;
    }

    public function isVisibleFor(?User $user = null): bool
    {
        if ($user === null) {
            return $this->visibility !== self::VISIBILITY_DISABLED_FOR_ALL;
        }

        if ($user->isAdmin()) {
            return $this->visibility === self::VISIBILITY_ENABLED_FOR_ALL;
        }

        return $this->visibility === self::VISIBILITY_ENABLED_FOR_ALL
            || $this->visibility === self::VISIBILITY_ENABLED_FOR_ALL_BUT_ADMIN;
    }
}
