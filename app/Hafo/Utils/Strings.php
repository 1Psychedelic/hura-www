<?php

namespace Hafo\Utils;

class Strings {

    static function uncamelize($s)
    {
        $s = preg_replace('#(.)(?=[A-Z])#', '$1-', $s);
        $s = strtolower($s);
        $s = rawurlencode($s);
        return $s;
    }

    static function camelize($s)
    {
        return lcfirst(str_replace('_', '', ucwords($s, '_')));
    }

}
