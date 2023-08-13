<?php

namespace VCD2\Firebase\Mapper;

use Hafo\Orm\Mapper\Mapper;
use Nextras\Orm\Entity\Reflection\PropertyMetadata;
use Nextras\Orm\Mapper\Dbal\DbalMapper;
use Nextras\Orm\Mapper\IMapper;

class FirebasePushTokenMapper extends Mapper {
    public function getTableName() : string {
        return 'firebase_push_token';
    }
}
