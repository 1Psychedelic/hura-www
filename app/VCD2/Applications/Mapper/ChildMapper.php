<?php

namespace VCD2\Applications\Mapper;

use Hafo\Orm\Mapper\Mapper;
use VCD2\Applications\Child;

class ChildMapper extends Mapper {

    protected $encrypted = ['encryptedName', 'encryptedHealth', 'encryptedAllergy', 'encryptedNotes'];

    public function getTableName() : string {
        return 'vcd_application_child';
    }

}
