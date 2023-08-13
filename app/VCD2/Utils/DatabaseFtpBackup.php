<?php

namespace VCD2\Utils;

use League\Flysystem\Filesystem;
use League\Flysystem\PhpseclibV2\SftpAdapter;
use League\Flysystem\PhpseclibV2\SftpConnectionProvider;
use Nette\SmartObject;

/**
 * @method onSuccess()
 * @method onError(\Throwable $e)
 */
class DatabaseFtpBackup {

    use SmartObject;

    public $onSuccess = [];

    public $onError = [];

    private $tmp;

    private $host;

    private $username;

    private $password;

    private $database;

    function __construct($tmp, $host, $username, $password, $database) {
        $this->tmp = $tmp;
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;
    }

    function performBackup() {
        try {
            $adapter= new SftpAdapter(
                new SftpConnectionProvider(
                    'zaxik.fun',
                    'testhurataborycz',
                    'VYs2zVHN9M7ZJMGrJeZfx5LWHo',
                    null,
                    null,
                    5514
                ),
                '/storage'
            );
            $filesystem = new Filesystem($adapter);

            $y = date('Y');
            $m = date('m');

            $stamp = date('Y-m-d_H-i-s');
            $randomness = \Nette\Utils\Random::generate(6);

            $tmpfileName = $this->tmp . "/db-{$stamp}_{$randomness}.sql";
            $tmpfile = fopen($tmpfileName, 'wb');

            $mysqli = new \mysqli($this->host, $this->username, $this->password, $this->database);
            $backup = new \MySQLDump($mysqli);
            $backup->write($tmpfile);

            fclose($tmpfile);

            $tmpfile = fopen($tmpfileName, 'rb');

            $filesystem->writeStream("/storage/{$this->database}/{$y}/{$m}/{$stamp}_{$randomness}.sql", $tmpfile);

            fclose($tmpfile);

            unlink($tmpfileName);

            $this->onSuccess();

        } catch (\Throwable $e) {
            print_r(get_class($e));
            print_r($e->getMessage());
            //print_r($e->getTrace());
            $this->onError($e);
        }

    }

}
