<?php

namespace Hafo\FileStorage\UrlAccessibleStorage;

use Hafo\FileStorage;
use Nette\IOException;
use Nette\Utils\FileSystem;

final class DefaultStorage implements FileStorage\UrlAccessibleStorage {

    private $dir;

    private $baseDir;

    private $baseUrl;

    /**
     * @param string $dir Current directory
     * @param string $baseDir Base directory that points to the location of Base url
     * @param string $baseUrl Base url
     */
    function __construct($dir, $baseDir, $baseUrl) {
        $this->dir = $dir;
        $this->baseDir = $baseDir;
        $this->baseUrl = $baseUrl;
    }

    function path() {
        return $this->dir;
    }

    function dir($name) {
        return new self(
            $this->dir . '/' . $name,
            $this->baseDir,
            $this->baseUrl
        );
    }

    function read($name) {
        return file_get_contents($this->dir . '/' . $name);
    }

    function write($name, $data) {
        FileSystem::createDir($this->dir);
        $path = $this->dir . '/' . $name;
        file_put_contents($this->dir . '/' . $name, $data);
        return $path;
    }

    function delete($name) {
        try {
            FileSystem::delete($this->dir . '/' . $name);
        } catch(IOException $e) {
            throw new FileStorage\StorageException($e->getMessage(), $e->getCode(), $e);
        }
    }

    function findFiles($mask, $recursive = FALSE) {
        if(!is_dir($this->dir)) {
            return [];
        }
        $finder = \Nette\Utils\Finder::findFiles($mask);
        if($recursive) {
            return $finder->from($this->dir);
        } else {
            return $finder->in($this->dir);
        }
    }

    function pathToUrl($path, $absolute = FALSE) {
        if(strpos($path, $this->baseDir) === 0) {
            return ($absolute ? $this->baseUrl . '/' : '') . str_replace($this->baseDir, '', $path);
        }
        throw new FileStorage\StorageException("Path '$path' is not inside baseDir '$this->baseDir' - cannot construct URL.");
    }

    function urlToPath($url) {
        if(strpos($url, $this->baseUrl) === 0) {
            return $this->baseDir . '/' . str_replace($this->baseUrl, '', $url);
        }
        throw new FileStorage\StorageException("Url '$url' is not under '$this->baseUrl' - cannot construct path.");
    }

}
