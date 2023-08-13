<?php

namespace VCD2\Users\Mapper;

use Hafo\Orm\Mapper\Mapper;
use Nextras\Orm\Entity\Reflection\PropertyMetadata;
use Nextras\Orm\Mapper\Dbal\DbalMapper;
use Nextras\Orm\Mapper\IMapper;

class ChildMapper extends Mapper {

    protected $encrypted = ['encryptedName', 'encryptedHealth', 'encryptedAllergy', 'encryptedNotes'];

    public function getTableName() : string {
        return 'vcd_child';
    }

    public function getManyHasManyParameters(PropertyMetadata $sourceProperty, DbalMapper $targetMapper) {
        switch(TRUE) {
            case $targetMapper instanceof UserMapper:
                return ['vcd_child_parent', ['child', 'parent']];
                break;
        }
        return parent::getManyHasManyParameters($sourceProperty, $targetMapper);
    }

}
