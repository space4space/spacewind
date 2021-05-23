<?php

namespace Spacewind;

class Page extends Element
{
    public $filters_bulk;
    public $filters;
    public $depth;
    public $url;
    public $path;

    public function __construct($name = null, $layout = null, $full_url = null)
    {
        global $site, $path, $pages;
        if (is_null($full_url)) {
            $full_url = $_SERVER['REQUEST_URI'];
        }

        if (is_null($name)) {
            $this->initFromUrl($full_url);
        } else {
            $this->properties = $pages->$name;
            parent::__construct($name);
        }

        if (!isset($this->properties->_id)) {
            header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
            $this->name = 'error-404';
            $this->properties = $pages->{$this->name};
            parent::__construct($this->name);
        }

        $this->properties->name = $this->name;

        if (!isset($this->properties->jsvars)) {
            $this->properties->jsvars = (object) ['dataurl' => $path['base'].'/data/'.$this->name.'/'];
            if (isset($path['images_url'])) {
                $this->properties->jsvars->uploaded_url = $path['images_url'];
            }
        }

        if (!isset($this->properties->dataurl_nofilters)) {
            $this->attachFiltersToUrl($this->properties->jsvars->dataurl);
        }

        if (!is_null($layout)) {
            $this->properties->layout = $layout;
        } else {
            if (!isset($this->properties->layout)) {
                if (isset($site->layout)) {
                    $this->properties->layout = $site->layout;
                } else {
                    $this->properties->layout = 'default';
                }
            }
        }
    }

    public function attachFiltersToUrl(&$url)
    {
        $filters = '';
        if (!empty($this->filters)) {
            foreach ($this->filters as $filter => $value) {
                $filters .= '/'.$filter.'/'.$value;
            }
            if (substr($url, -1) == '/') {
                $url = $url.$filters;
            } else {
                $url = $url.'/'.$filters;
            }
        }

        return true;
    }

    public function initFromUrl($full_url)
    {
        global $site, $pages;
        if (substr($full_url, 0, 2) == '//') {
            $full_url = substr_replace($full_url, 'index', 1, 0);
        }

        $this->url = parse_url($full_url);
        $web_path = explode('/', $this->url['path']);

        // $this->url=$full_url;
        // $web_path=explode('/', $this->url);
        $this->path = $web_path;
        if (isset($site->url)) {
            $this->depth = count_chars($site->url, 0)[47] - 1;
        } else {
            $this->depth = 1;
        }
        do {
            if (isset($web_path[$this->depth]) && $web_path[$this->depth] != '') {
                $name = $web_path[$this->depth];
            } else {
                $name = 'index';
            }
            $this->properties = $pages->$name;
            parent::__construct($name);
            ++$this->depth;
            // debug($name);
        // debug($this->depth);
        } while (isset($this->properties->subpages[$this->depth])
            && isset($web_path[$this->depth])
            && in_array($web_path[$this->depth], $this->properties->subpages[$this->depth]));

        $this->filters_bulk = array_chunk(array_slice($web_path, $this->depth), 2);

        foreach ($this->filters_bulk as $f) {
            if (!isset($f[1])) {
                $f[1] = '';
            }
            $this->filters[urldecode($f[0])] = urldecode($f[1]);
        }

        return $name;
    }
}
