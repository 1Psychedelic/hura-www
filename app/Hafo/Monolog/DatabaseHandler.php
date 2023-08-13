<?php

namespace Hafo\Monolog;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Nette\Database\Context;

class DatabaseHandler extends AbstractProcessingHandler {

    const DEFAULT_FIELDS = ['channel', 'level', 'message', 'created_at'];

    private $database;

    private $tableName = 'monolog';

    private $extraFields = [];
    
    function __construct(Context $database, $tableName = 'monolog', $extraFields = [], $level = Logger::DEBUG, $bubble = TRUE) {
        parent::__construct($level, $bubble);
        $this->tableName = $tableName;
        $this->extraFields = $extraFields;
        $this->database = $database;
    }

    protected function write(array $record)
    {
        $data = array_intersect_key($record, array_fill_keys(array_merge(self::DEFAULT_FIELDS, $this->extraFields), NULL));
        $this->database->table($this->tableName)->insert($data);
    }


}
