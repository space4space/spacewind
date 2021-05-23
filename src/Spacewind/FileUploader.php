<?php

namespace Spacewind;

class FileUploader
{
    public $config = ['MaxSize' => 10048576];
    public $error;
    public $tmp;
    public $filename;
    public $full_path;
    public $target;
    public $name;
    public $ext;
    public $type;
    public $size;
    public $img;

    public function __construct($file, $target = null)
    {
        global $path, $site;

        $this->target = $target;
        $exploded=explode('.', $file['name']);
        $this->name = implode(".", array_slice($exploded, 0, -1));
        $this->ext = end($exploded);
        $this->type = $file['type'];
        $this->size = $file['size'];
        $this->tmp = $file['tmp_name'];

        if ($file['error'] > 0) {
            $this->error .= "Error: File['error']={$file['error']}.\n";

            return false;
        }

        if ($file['size'] > $this->config['MaxSize']) {
            $this->error .= "Error: File is too big ({$file['size']}>{$this->config['MaxSize']}).\n";

            return false;
        }

        if (is_null($this->target)) {
            $this->target = $site->path.$path['assets'].$path['upload'];
        }

        return true;
    }

    public function save()
    {
        if (file_exists($this->tmp) && is_uploaded_file($this->tmp)) {
            $addition = null;

            do {
                $this->filename = $this->name.$addition++.'.'.$this->ext;
                $this->full_path = $this->target.$this->filename;
            } while (file_exists($this->full_path));

            move_uploaded_file($this->tmp, $this->full_path);

            return true;
        } else {
            $this->error .= "Error: File not uploaded or deleted.\n";

            return false;
        }
    }
}
