<?php
declare(strict_types=1);

namespace Hafo\Google\Analytics\Analytics;

use Hafo\Google\Analytics\Event;
use Nette\Http\Session;
use Nette\Http\SessionSection;

class Analytics implements \Hafo\Google\Analytics\Analytics
{
    /** @var SessionSection */
    private $session;

    /** @var string */
    private $analyticsId;

    public function __construct(Session $session, string $analyticsId)
    {
        $this->session = $session->getSection('google.analytics');
        $this->analyticsId = $analyticsId;
    }

    public function addEvent(string $category, string $action, ?string $label = null, ?int $value = null): void
    {
        $key = md5($category . $action . $label . $value);
        $this->session[$key] = serialize(new Event($category, $action, $label, $value));
    }

    public function getAnalyticsHtml(): string
    {
        $initCode = <<<CODE
<script type="text/javascript">
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
    ga('create', '%s', 'auto');
    ga('send', 'pageview');
    %s
</script>
CODE;

        $html = '';
        foreach ($this->session as $key => $event) {
            $eventObj = unserialize($event);
            $html .= "\n" . $eventObj->getHtml();
            unset($this->session[$key]);
        }

        return sprintf($initCode, $this->analyticsId, $html);
    }
}
