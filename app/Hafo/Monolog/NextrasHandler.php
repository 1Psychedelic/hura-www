<?php

namespace Hafo\Monolog;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Nextras\Dbal\Connection;

class NextrasHandler extends AbstractProcessingHandler
{
    const DEFAULT_FIELDS = ['channel' => '%s', 'level' => '%i', 'message' => '%s', 'created_at' => '%dt'];

    private $connection;

    private $tableName = 'monolog';

    private $extraFields = [];

    public function __construct(Connection $connection, $tableName = 'monolog', $extraFields = [], $level = Logger::DEBUG, $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->tableName = $tableName;
        $this->extraFields = $extraFields;
        $this->connection = $connection;
    }

    protected function write(array $record)
    {
        $context = $record['context'];
        unset($record['context']);
        $record = array_merge($context, $record);

        $fields = array_merge(self::DEFAULT_FIELDS, $this->extraFields);
        $sql = 'INSERT INTO monolog (' . implode(', ', array_keys($fields)) . ') VALUES (' . implode(', ', array_values($fields)) . ')';
        $record['created_at'] = new \DateTime;
        $data = array_intersect_key($record, $fields);
        $sorted = [];
        foreach ($fields as $field => $foo) {
            $sorted[$field] = $data[$field] ?? null;
        }

        try {
            $this->connection->queryArgs($sql, $sorted);
        } catch (\Throwable $e) {
        }
    }
}
