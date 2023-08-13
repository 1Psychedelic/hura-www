<?php

namespace Hafo\DatabaseMigration\Migrator;

use Hafo\DatabaseMigration;
use Nette\Database\Context;
use Nette\Utils\Finder;
use Tracy\Debugger;

class DefaultMigrator implements DatabaseMigration\Migrator {

    private $database;

    private $migrations;

    function __construct(Context $database, array $migrations) {
        $this->database = $database;
        $this->migrations = $migrations;
    }

    function run() {
        $output = [
            "\n\nExecuting migrations..."
        ];
        $this->database->beginTransaction();
        $done = $this->database->table('database_migration')->where('performed_at IS NOT NULL')->fetchPairs('id', 'name');
        foreach($this->migrations as $name => $sqls) {
            if(in_array($name, $done)) {
                continue;
            }
            $ok = TRUE;
            foreach($sqls as $sql) {
                try {
                    $this->database->query($sql);
                    $output[] = $name . ': OK';
                } catch (\PDOException $e) {
                    $output[] = $name . ': ' . $e->getMessage();
                    $ok = FALSE;
                    Debugger::log($e);
                }
            }
            if($ok) {
                $this->database->table('database_migration')->insert(['name' => $name, 'performed_at' => new \DateTime]);
            }
        }
        $this->database->commit();

        $output[] = "Migrations done.\n\n";

        echo implode("\n", $output);
    }

}
