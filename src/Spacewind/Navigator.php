<?php

namespace Spacewind;

class Navigator
{
    private $page;
    public $tree;
    public $breadcrumbs;
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
        $this->tree = $config->navigation;
        $this->breadcrumbs = [];
        $this->page_level_find = 100;
        $this->generateItems($this->tree);

        return $this;
    }

    public function create($page)
    {
        if (isset($page)) {
            $this->page = $page;
            $this->markItems($this->tree, 0);
        }

        return $this->tree;
    }

    private function markItems(&$list, $level)
    {
        $result = false;
        foreach ($list as &$item) {
            $result = false;
            if (!isset($item->page)) {
                if (isset($item->link)) {
                    $path = parse_url($item->link, PHP_URL_PATH);
                    $segments = explode('/', rtrim($path, '/'));
                    $item->page = end($segments);
                    if ($item->link == '/') {
                        $item->page = 'index';
                    }
                } else {
                    $item->link = null;
                    $item->page = null;
                }
            }
            if (empty($item->noscan)) {
                if ($item->exist_groups) {
                    $result = $this->markItems($item->groups, $level);
                } elseif ($item->exist_submenu) {
                    $result = $this->markItems($item->submenu, $level + 1);
                }
            }

            if ($item->page == $this->page->name && $this->page_level_find > $level) {
                $this->page_level_find = $level;
                $this->breadcrumbs = [];
                $result = true;
            }

            if (isset($this->config->class_active) && $result) {
                $item->class .= ' '.$this->config->class_active;

                array_unshift($this->breadcrumbs, (object) ['title' => $item->title, 'link' => $item->link]);

                return $result;
            }

            if ($result) {
                $this->breadcrumbs[$level] = (object) ['title' => $item->title, 'link' => $item->link];
            }
        }

        return $result;
    }

    public function generateItems(&$list)
    {
        foreach ($list as &$item) {
            if (!isset($item->class)) {
                $item->class = false;
            }
            if (!isset($item->icon)) {
                $item->icon = false;
            }

            if (isset($item->list)) {
                $item->submenu = $this->createListFromModel($item->list);
            }
            if (isset($item->list_j)) {
                $item->submenu = $this->createListFromConfig($item->list_j);
            }
            if (isset($item->submenu)) {
                $this->generateItems($item->submenu);
            }

            $item->exist_submenu = isset($item->submenu);
            $item->exist_groups = isset($item->groups);
        }

        return true;
    }

    public function createListFromConfig($item)
    {
        global $path;
        $result = array();
        $conf = new \Spacewind\FilesDB\Database($path[$item->app]);
        $source = $conf->{$item->collection};
        $elements = $source->find();

        foreach ($elements as $element) {
            array_push(
                $result,
                (object) [
                    'link' => $item->path.$element['_id'],
                    'title' => $element['_id'],
                ]
            );
        }

        return $result;
    }

    public function createListFromModel($item)
    {
        $result = array();
        $source = new $item->class();

        if (isset($item->filter)) {
            $source->where($item->filter->field, $item->filter->value);
        }

        $elements = $source->get();

        foreach ($elements as $element) {
            array_push(
                $result,
                (object) [
                    'link' => $item->path.$element->id,
                    'title' => $element->name,
                    'toggle' => $item->toggle,
                ]
            );
        }

        return $result;
    }
}
