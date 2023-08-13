<?php

namespace VCD2\Users\Mapper;

use Hafo\Orm\Mapper\Mapper;
use Nextras\Orm\Entity\Reflection\PropertyMetadata;
use Nextras\Orm\Mapper\Dbal\DbalMapper;
use Nextras\Orm\Mapper\IMapper;

class ConsentMapper extends Mapper {

    protected $encrypted = ['encryptedEmail', 'encryptedIp'];

    public function getTableName() : string {
        return 'vcd_consent';
    }

}
