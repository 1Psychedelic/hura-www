<?php

namespace Hafo\Utils;

class HttpHelper {

    static function uploadMaxSize() {
        return min(
            self::bytes(ini_get('upload_max_filesize')),
            self::bytes(ini_get('post_max_size')),
            self::bytes(ini_get('memory_limit'))
        );
    }

    static private function bytes($val) {
        $val = trim($val);
        $last = strtolower($val[strlen($val)-1]);
        switch($last) {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }
        return $val;
    }

}
