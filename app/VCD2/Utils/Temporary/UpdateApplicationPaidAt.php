<?php
declare(strict_types=1);

namespace VCD2\Utils\Temporary;

use VCD2\Orm;

class UpdateApplicationPaidAt
{
    /** @var Orm */
    private $orm;

    public function __construct(Orm $orm)
    {
        $this->orm = $orm;
    }

    public function execute()
    {
        set_time_limit(0);
        $selection = $this->orm->applications->findAll();
        //$selection = $this->orm->applications->findBy(['id>' => 30825]);
        foreach ($selection as $application) {
            $application->updatePaidAt();
            $this->orm->persistAndFlush($application);
        }
    }
}
