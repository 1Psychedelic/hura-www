<?php
declare(strict_types=1);

namespace Hafo\Google\Analytics\Analytics;

use Hafo\Google\Analytics\Event;
use Nette\Http\Session;
use Nette\Http\SessionSection;

class TestAnalytics implements \Hafo\Google\Analytics\Analytics
{
    /** @var SessionSection */
    private $session;

    public function __construct(Session $session)
    {
        $this->session = $session->getSection('google.analytics.test');
    }

    public function addEvent(string $category, string $action, ?string $label = null, ?int $value = null): void
    {
        $key = md5($category . $action . $label . $value);
        $this->session[$key] = serialize(new Event($category, $action, $label, $value));
    }

    public function getAnalyticsHtml(): string
    {
        foreach ($this->session as $key => $event) {
            $eventObj = unserialize($event);
            \Tracy\Debugger::barDump($eventObj, 'Sending GA event');
            unset($this->session[$key]);
        }

        return '';
    }
}
