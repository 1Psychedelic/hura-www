<?php
/*if(php_sapi_name() !== 'cli') {
    list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':', base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
    if (!isset($_SERVER['PHP_AUTH_USER']) || $_SERVER['PHP_AUTH_USER'] !== 'vcd' || $_SERVER['PHP_AUTH_PW'] !== 'kutik') {
        header('WWW-Authenticate: Basic realm="Vstup povolen pouze s usmevem!"');
        header('HTTP/1.0 401 Unauthorized');
        die;
    }
}*/

$maintenance = require_once(__DIR__ . '/../.maintenance.php');

if($maintenance !== FALSE) {
    if(!isset($_COOKIE['maintenance']) || $_COOKIE['maintenance'] !== 'aláírčewfgfdmůkljsnadf,r546adfs456dgfhojušočknlasdf') {
        header('HTTP/1.1 503 Service Temporarily Unavailable');
        header('Status: 503 Service Temporarily Unavailable');
        header('Retry-After: 300'); // seconds

        $fileHtm = __DIR__ . '/.maintenance-' . $maintenance . '.htm';
        if(!is_bool($maintenance) && file_exists($fileHtm)) {
            die(file_get_contents($fileHtm));
        } else {
            die(file_get_contents(__DIR__ . '/.maintenance.htm'));
        }
    }
}

require __DIR__ . '/../app/bootstrap-admin.php';
