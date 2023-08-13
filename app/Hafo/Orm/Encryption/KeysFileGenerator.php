<?php

namespace Hafo\Orm\Encryption;

use Defuse\Crypto\Key;
use Hafo\Orm\Mapper\Mapper;
use Nette\Utils\Finder;

class KeysFileGenerator {

    private $keysFile;

    function __construct($keysFile) {
        $this->keysFile = $keysFile;
    }

    function initialGenerate($path, $overwriteExistingKeys = FALSE) {
        $keys = [];
        foreach(Finder::findFiles('*Mapper.php')->from($path) as $name => $finfo) {
            $class = self::scanPhp($name);
            if(!$class) {
                continue;
            }
            $refl = new \ReflectionClass($class);
            if($refl->isAbstract() || $refl->isInterface()) {
                continue;
            }
            $mapper = $refl->newInstanceWithoutConstructor();
            if(is_a($mapper, Mapper::class)) {
                $table = $mapper->getTableName();
                $prop = $refl->getProperty('encrypted');
                $prop->setAccessible(TRUE);
                $fields = $prop->getValue($mapper);
                $prop->setAccessible(FALSE);

                if(!array_key_exists($table, $keys)) {
                    $keys[$table] = [];
                }
                foreach($fields as $key => $val) {
                    $field = is_numeric($key) ? $val : $key;
                    $keys[$table][$field] = Key::createNewRandomKey()->saveToAsciiSafeString();
                }
            }
        }

        $data = [];
        if(file_exists($this->keysFile)) {
            $data = include $this->keysFile;
        }
        foreach($keys as $table => $fields) {
            if(!array_key_exists($table, $data)) {
                $data[$table] = [];
            }
            foreach($fields as $field => $key) {
                if($overwriteExistingKeys || !array_key_exists($field, $data[$table])) {
                    $data[$table][$field] = $key;
                }
            }
            if(empty($data[$table])) {
                unset($data[$table]);
            }
        }
        $arrayString = var_export($data, TRUE);
        file_put_contents($this->keysFile, "<?php\nreturn " . $arrayString . ";");
    }

    static private function scanPhp($file) {
        $fp = fopen($file, 'r');
        $class = $namespace = $buffer = '';
        $i = 0;
        while (!$class) {
            if (feof($fp)) break;

            $buffer .= fread($fp, 512);
            $tokens = token_get_all($buffer);

            if (strpos($buffer, '{') === false) continue;

            for (;$i<count($tokens);$i++) {
                if ($tokens[$i][0] === T_NAMESPACE) {
                    for ($j=$i+1;$j<count($tokens); $j++) {
                        if ($tokens[$j][0] === T_STRING) {
                            $namespace .= '\\'.$tokens[$j][1];
                        } else if ($tokens[$j] === '{' || $tokens[$j] === ';') {
                            break;
                        }
                    }
                }

                if ($tokens[$i][0] === T_CLASS) {
                    for ($j=$i+1;$j<count($tokens);$j++) {
                        if ($tokens[$j] === '{') {
                            $class = $tokens[$i+2][1];
                        }
                    }
                }
            }
        }
        return $namespace . '\\' . $class;
    }

}
