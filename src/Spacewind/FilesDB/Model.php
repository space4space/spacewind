<?php

namespace Spacewind\FilesDB;

class Model extends Collection
{
    public $filesdb = true;
    public $id;
    protected $db;
    protected $collection;
    protected $target;

    public function __construct()
    {
        if (is_dir($this->db.$this->collection)) {
            $this->target = $this->db.$this->collection;
        } else {
            throw new \Exception('FilesDB Error: Base target not found');
        }
        parent::__construct($this->target);
    }

    public function get()
    {
        return iterator_to_array(new ItemIterator($this), false);
    }

    public function create($values)
    {
        $id = $values['_id'];
        unset($values['_id']);
        $this->save($id, $values);

        return $this;
    }
}
