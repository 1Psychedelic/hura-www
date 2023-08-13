<?php

namespace VCD2\Users\Mapper;

use Hafo\Orm\Mapper\Mapper;
use Nextras\Orm\Entity\Reflection\PropertyMetadata;
use Nextras\Orm\Mapper\Dbal\DbalMapper;
use Nextras\Orm\Mapper\IMapper;

class UserSessionMapper extends Mapper {

    public function getTableName() : string {
        return 'system_user_session';
    }

}
