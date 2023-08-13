<?php
declare(strict_types=1);

namespace Hafo\Google\Analytics\Analytics;

use Hafo\Google\Analytics\Analytics;

class NoAnalytics implements Analytics
{
    public function addEvent(string $category, string $action, ?string $label = null, ?int $value = null): void
    {
    }

    public function getAnalyticsHtml(): string
    {
        return '';
    }
}
