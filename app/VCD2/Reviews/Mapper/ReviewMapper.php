<?php
declare(strict_types=1);

namespace VCD2\Reviews\Mapper;

use Hafo\Orm\Mapper\Mapper;

class ReviewMapper extends Mapper
{
    public function getTableName() : string
    {
        return 'vcd_review';
    }

    public function findRandom()
    {
        $builder = $this->builder()
            ->where('score = 5')
            ->orderBy('RAND()');

        return $this->toCollection($builder);
    }
}
