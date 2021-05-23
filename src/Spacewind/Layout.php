<?php

namespace Spacewind;

class Layout extends Element
{
    const COLLECTION = 'layouts';

    public function __construct($name)
    {
        global $cfg, $site, $filters, $page, $layouts;

        if (isset($filters) && array_key_exists('layout', $filters)) {
            if ($filters['layout'] == '') {
                setcookie('layout', '', -1, '/');
            } else {
                setcookie('layout', $filters['layout'], time() + 31104000, '/');
            }

            if ($page->name == 'index') {
                $page = '';
            } else {
                $page = $page->name;
            }
            $url = $site->protocol.'://'.$site->domain.'/'.$page;
            unset($filters['layout']);
            foreach ($filters as $key => $value) {
                if ($value != '') {
                    $url .= '/'.$key.'/'.$value;
                }
            }
            header('Location: '.$url);
            die();
        }

        if (isset($_COOKIE['layout'])) {
            $name = $_COOKIE['layout'];
        }

        if (!isset($layouts->$name->_id)) {
            $name = 'default';
        }

        $this->properties = $layouts->$name;

        parent::__construct($name);

        $this->properties->theme = (object) array($name => true);
    }

    public function getTemplate()
    {
        global $path;

        return file_get_contents($path['views'].'layouts/'.$this->name.'.mustache');
    }
}
