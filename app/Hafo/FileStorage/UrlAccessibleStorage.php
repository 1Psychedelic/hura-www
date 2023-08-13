<?php

namespace Hafo\FileStorage;

interface UrlAccessibleStorage extends FileStorage {

    /**
     * @param string $path
     * @param bool $absolute
     * @return string
     * @throws StorageException
     */
    function pathToUrl($path, $absolute = FALSE);

    /**
     * @param string $url
     * @return string
     * @throws StorageException
     */
    function urlToPath($url);

}
