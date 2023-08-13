<?php

use Hafo\DI\Container;

return [

    \VCD2\Utils\DatabaseFtpBackup::class => function(Container $c) {
        if (file_exists(__DIR__ . '/../local/db-full.php')) {
            $db = require __DIR__ . '/../local/db-full.php';
        } else {
            $db = require __DIR__ . '/../db-full.php';
        }

        $log = $c->get(\Monolog\Logger::class);

        $backup = new \VCD2\Utils\DatabaseFtpBackup(
            $c->get('tmp'),
            $db['host'],
            $db['username'],
            $db['password'],
            $db['database']
        );

        $backup->onSuccess[] = function() use ($c, $log) {
            $c->get(\Nextras\Dbal\Connection::class)->query('DELETE FROM monolog WHERE created_at < DATE_SUB(NOW(), INTERVAL 14 DAY)');
            $log->info('Záloha db OK!');
        };

        $backup->onError[] = function(\Throwable $e) use ($log) {
            $file = \Tracy\Debugger::log($e);

            $log->critical(sprintf('Záloha db FAIL! %s "%s": %s', get_class($e), $e->getMessage(), $file));
        };

        return $backup;
    },

];
