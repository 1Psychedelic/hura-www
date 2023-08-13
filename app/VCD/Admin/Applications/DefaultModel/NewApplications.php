<?php

namespace VCD\Admin\Applications\DefaultModel;

use Nette\Database\Context;

class NewApplications implements \VCD\Admin\Applications\NewApplications
{
    private $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    public function count()
    {
        return $this->database->table('vcd_application')
            ->select('COUNT(id)')
            ->where('is_applied = 1 AND is_accepted = 0 AND is_rejected = 0 AND is_canceled = 0')
            ->fetchField();
    }
}
