<?php

namespace Hafo\Utils;

class Arrays {

	/**
	 * Recursive array diff
	 * @param array $arr1
	 * @param array $arr2
	 * @return array
	 */
	static function diff(array $arr1, array $arr2) {
		$r = [];
		foreach($arr1 as $key => $value) {
			if(array_key_exists($key, $arr2)) {
				if(is_array($value)) {
					$diff = self::diff($value, $arr2[$key]);
					if(count($diff)) {
						$r[$key] = $diff;
					}
				} else {
					if($value != $arr2[$key]) {
						$r[$key] = $value;
					}
				}
			} else {
				$r[$key] = $value;
			}
		}
		return $r;
	}

    static function union(array &$ref, array $append) {
        foreach($append as $key => $value) {
            $ref[$key] = $value;
        }
    }

    static function append(array &$ref, array $append) {
        foreach($append as $key => $value) {
            $ref[] = $value;
        }
    }

    static function unionAppend(array &$ref, array $append) {
        foreach($append as $key => $value) {
            if(array_key_exists($key, $ref)) {
                if(!is_array($ref[$key])) {
                    $existing = $ref[$key];
                    $ref[$key] = [$existing];
                }
                $ref[$key][] = $value;
            } else {
                $ref[$key] = $value;
            }
        }
    }
    
    static function subArrayByPrefix(array &$data, $prefix, $removeFromArray = FALSE, $returnWithoutPrefix = FALSE) {
        $ret = array_intersect_key($data, array_flip(preg_grep('/^' . $prefix . '/', array_keys($data))));
        if($removeFromArray) {
            foreach(array_keys($ret) as $key) {
                unset($data[$key]);
            }
        }
        if($returnWithoutPrefix) {
            $tmp = $ret;
            $ret = [];
            foreach($tmp as $key => $val) {
                $ret[substr($key, strlen($prefix))] = $val;
            }
        }
        return $ret;
    }

}
