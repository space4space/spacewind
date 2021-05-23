<?php

namespace Spacewind;

class CustomLoader extends \Mustache_Loader_FilesystemLoader
{
    private $page;
    private $layout;
    private $baseDir;

    public function __construct($baseDir, array $options = array())
    {
        $this->page_name = isset($options['page']) ? $options['page'] : 'index';
        $this->layout_name = isset($options['layout']) ? $options['layout'] : 'default';
        $this->asset_name = isset($options['asset']) ? $options['asset'] : 'default';
        $this->baseDir = $baseDir;

        parent::__construct($baseDir, $options);
    }

    public function load($name)
    {
        $parts = explode('.', $name, 2);
        $type = $parts[0];

        if ($type != 'page' and $type != 'layout' and $type != 'asset') {
            return '';
        }

        $file = str_replace('.', '/', $parts[1]);
        if ($type == 'page' and $file == 'content') {
            return parent::load("{$type}s/{$this->page_name}.mustache");
        }

        $item_name = $type.'_name';
        $item_name = $parts = explode('.', $this->$item_name, 2)[0];

        $path = "{$type}s/partials/{$item_name}/{$file}.mustache";

        if (!is_file($this->baseDir.$path)) {
            $path = "{$type}s/partials/_shared/{$file}.mustache";
            if (!is_file($this->baseDir.$path)) {
                return '';
            }
        }

        return parent::load($path);
    }
}
