<?php

namespace VCD2\Events\Mapper;

use DateTimeImmutable;
use Hafo\Http\CacheHeaders;
use Hafo\Orm\Mapper\Mapper;
use Nextras\Orm\Entity\Reflection\PropertyMetadata;
use Nextras\Orm\Mapper\Dbal\DbalMapper;
use VCD2\Discounts\Mapper\DiscountCodeMapper;
use VCD2\Users\Mapper\UserMapper;

class EventMapper extends Mapper
{
    public function getTableName() : string
    {
        return 'vcd_event';
    }

    public function getManyHasManyParameters(PropertyMetadata $sourceProperty, DbalMapper $targetMapper)
    {
        switch (true) {
            case $targetMapper instanceof DiscountCodeMapper:
                return ['vcd_discount_event', ['event', 'discount']];

                break;
            case $targetMapper instanceof UserMapper:
                return ['vcd_event_user', ['event', 'user']];

                break;
        }

        return parent::getManyHasManyParameters($sourceProperty, $targetMapper);
    }

    public function getUpcomingCacheHeaders()
    {
        $sql = <<<SQL
SELECT GROUP_CONCAT(e.id) AS ids, MAX(e.updated_at) AS updated_at, GROUP_CONCAT(d.id) AS current_price_discount_ids
FROM vcd_event e
LEFT JOIN vcd_discount d ON d.event = e.id
WHERE e.visible = 1 AND e.is_archived = 0 AND d.starts < NOW() AND d.ends > NOW()
SQL;

        $result = $this->connection->query($sql);
        $row = $result->fetch();

        if ($row === null) {
            return null;
        }

        $row = $row->toArray();

        $etag = md5($row['ids'] . $row['updated_at'] . $row['current_price_discount_ids']);
        return new CacheHeaders($etag, new DateTimeImmutable($row['updated_at']));
    }

    public function countUpcoming($type = null, $includeHidden = false, $strict = false)
    {
        $builder = $this->createUpcomingBuilder($type, $includeHidden, $strict)
            ->select('COUNT(vcd_event.id)');

        return (int)$this->connection->queryArgs($builder->getQuerySql(), $builder->getQueryParameters())->fetchField();
    }

    public function findUpcoming($type = null, $includeHidden = false, $strict = false)
    {
        $builder = $this->createUpcomingBuilder($type, $includeHidden, $strict)
            ->select('vcd_event.*');
//        $builder->leftJoin('vcd_event', 'vcd_application', 'a', 'a.event = vcd_event.id');
//        $builder->leftJoin('a', 'vcd_application_child', 'c', 'a.id = c.application AND a.applied_at IS NOT NULL AND a.accepted_at IS NOT NULL AND a.canceled_at IS NULL AND a.rejected_at IS NULL');
//        $builder->orderBy('IF(max_participants + max_reserves - COUNT(c.id) > 0, 1, 0) DESC, starts ASC');
//        $builder->groupBy('vcd_event.id');

        return $this->toCollection($builder);
    }

    public function findArchived()
    {
        $builder = $this->builder()
            ->where('is_archived = 1 AND visible = 1')
            ->orderBy('starts DESC');

        return $this->toCollection($builder);
    }

    public function findArchivedRandom()
    {
        $builder = $this->builder()
            ->where('is_archived = 1 AND visible = 1')
            ->orderBy('RAND()');

        return $this->toCollection($builder);
    }

    public function findSelectOptionsForAdmin()
    {
        $builder = $this->builder()
            ->select('id, CONCAT("#", id, " ", name, " (", DAY(starts), ".", MONTH(starts), ".", YEAR(starts), " - ", DAY(ends), ".", MONTH(ends), ".", YEAR(ends), ")") AS event')
            //->select('id, CONCAT("#", id, " ", name, ", ", price, " Kč") AS event')
            ->orderBy('starts DESC');

        return $this->connection->queryArgs($builder->getQuerySql(), $builder->getQueryParameters())->fetchPairs('id', 'event');
    }

    public function getCurrentEvent()
    {
        $builder = $this->builder()->where('visible = 1 AND starts < NOW() AND ends > NOW()');

        return $this->toEntity($builder);
    }

    private function createUpcomingBuilder($type = null, $includeHidden = false, $strict = false)
    {
        $builder = $this->builder()
            ->where('is_archived = 0');
        if ($type !== null) {
            $builder->andWhere('type = %i', $type);
        }
        if (!$includeHidden) {
            $builder->andWhere('visible = 1');
        }
        if ($strict) {
            $builder->andWhere('starts > NOW()');
        }

        return $builder;
    }
}
