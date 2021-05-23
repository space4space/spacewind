<?php

namespace Spacewind\FilesDB;

/**
 * FilesDB core
 * Management of collections.
 */
class Database
{
    protected $path = './';
    protected $collections = array();

    public function __construct($path)
    {
        if ($this->checkPath($path)) {
            $this->path = $path;
        }
    }

    protected function getCollection($collection)
    {
        return $this->collections[$collection] ?? new Collection($this->path.$collection);
    }

    public function listCollections()
    {
        $collections_list = [];
        if ($handle = opendir($this->path)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != '.' && $entry != '..') {
                    $collections_list[] = basename($entry);
                }
            }
            closedir($handle);
        }

        return $collections_list;
    }

    public function __call($op, $args)
    {
        return $this->getCollection($op);
    }

    public function __get($op)
    {
        return $this->getCollection($op);
    }

    public function checkPath($path)
    {
        if (!is_dir($path)) {
            throw new \Exception('FilesDB Error: Base path not found');
        }

        return true;
    }
}
