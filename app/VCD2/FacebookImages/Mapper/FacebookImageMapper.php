<?php
declare(strict_types=1);

namespace VCD2\FacebookImages\Mapper;

use Hafo\Orm\Mapper\Mapper;

class FacebookImageMapper extends Mapper
{
    public function getTableName(): string
    {
        return 'vcd_facebook_image';
    }
}
