<?php

namespace Spacewind\FilesDB;

/**
 * FilesDB Collection
 * Management collection of documents (folders).
 */
class Collection
{
    public $folder;
    public $items = array();
    protected $filetype = '*.json';

    public function __construct($folder)
    {
        $this->folder = $folder;
        $this->createItems();
    }

    private function createItems()
    {
        $filelist = shell_exec('cd '.$this->folder.'; dir '.$this->filetype.' -1');
        $files = explode("\n", $filelist);
        array_pop($files); // last line is always blank

        foreach ($files as $file) {
            $path_parts = pathinfo($file);
            $this->items[] = $path_parts['filename'];
        }
    }

    public function __destruct()
    {
    }

    public function findOne($keyArr)
    {
        $file = $this->folder.'/'.$keyArr['_id'].'.json';

        return $this->getFromFile($file);
    }

    public function getFromFile($file)
    {
        $path = pathinfo($file);
        if (is_file($file)) {
            $info = json_decode(file_get_contents($file), true);
            $info['_id'] = $path['filename'];

            return $info;
        } else {
            return false;
        }
    }

    public function find($id = null)
    {
        if ($id == null) {
            return new ItemIterator($this);
        } else {
            return $this->findOne($id);
        }
    }

    public function save($id = null, $value = null)
    {
        if ($id == null) {
        } else {
            $file = $this->folder.'/'.$id.'.json';
            unset($value->_id);
            file_put_contents($file, json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }

        return true;
    }
}
