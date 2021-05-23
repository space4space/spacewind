<?php

namespace Spacewind\FilesDB;

class ItemIterator implements \Iterator
{
    private $position = 0;

    public function __construct($collection)
    {
        $this->position = 0;
        $this->collection = $collection;
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function current()
    {
        return $this->collection->findOne(['_id' => $this->collection->items[$this->position]]);
    }

    public function key()
    {
        return $this->collection->items[$this->position];
    }

    public function next()
    {
        ++$this->position;
    }

    public function valid()
    {
        return isset($this->collection->items[$this->position]);
    }
}
