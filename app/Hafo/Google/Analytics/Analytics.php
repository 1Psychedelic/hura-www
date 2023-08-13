<?php
declare(strict_types=1);

namespace Hafo\Google\Analytics;

interface Analytics
{
    public const STATE_DISABLED = 0;
    public const STATE_ENABLED = 1;
    public const STATE_TEST = 2;

    public function addEvent(string $category, string $action, ?string $label = null, ?int $value = null): void;

    public function getAnalyticsHtml(): string;
}
