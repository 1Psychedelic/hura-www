<?php

namespace Hafo\FileStorage;

interface FileStorage {

    /**
     * Returns a current path
     *
     * @return string
     */
    function path();

    /**
     * Goes to a different directory (returns a new instance)
     *
     * @param string $name
     * @return static
     */
    function dir($name);

    /**
     * Returns a file's content
     *
     * @param string $name
     * @return mixed
     * @throws StorageException
     */
    function read($name);

    /**
     * Writes content into a file
     *
     * @param string $name
     * @param mixed $data
     * @throws StorageException
     */
    function write($name, $data);

    /**
     * Deletes a file or directory
     *
     * @param string $name
     * @throws StorageException
     */
    function delete($name);

    /**
     * Search for files
     *
     * @param $mask
     * @param bool $recursive
     * @return \SplFileInfo[]
     */
    function findFiles($mask, $recursive = FALSE);

}
