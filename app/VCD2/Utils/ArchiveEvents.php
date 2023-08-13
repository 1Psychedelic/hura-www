<?php
declare(strict_types=1);

namespace VCD2\Utils;

use Nextras\Dbal\Connection;

class ArchiveEvents
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute()
    {
        $this->connection->query('UPDATE vcd_event SET is_archived = 1 WHERE is_archived = 0 AND ends < NOW()');
    }
}
