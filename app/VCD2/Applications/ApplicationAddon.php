<?php
declare(strict_types=1);

namespace VCD2\Applications;

use Nextras\Orm\Relationships\ManyHasOne;
use VCD2\Entity;
use VCD2\Events\EventAddon;

/**
 * @property int $id {primary}
 * @property ManyHasOne|EventAddon $addon {m:1 EventAddon::$applicationAddons}
 * @property ManyHasOne|Application $application {m:1 Application::$addons}
 * @property int $amount {default 0}
 * @property int $price
 */
class ApplicationAddon extends Entity
{
    public function __construct(EventAddon $eventAddon, Application $application)
    {
        parent::__construct();
        $this->addon = $eventAddon;
        $this->application = $application;
        $this->price = $eventAddon->price;
    }
}
