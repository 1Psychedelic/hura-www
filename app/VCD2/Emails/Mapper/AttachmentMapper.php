<?php

namespace VCD2\Emails\Mapper;

use Hafo\Orm\Mapper\Mapper;

class AttachmentMapper extends Mapper {

    public function getTableName() : string {
        return 'vcd_email_attachment';
    }

}
