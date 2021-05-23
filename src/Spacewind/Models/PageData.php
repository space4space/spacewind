<?php

namespace Spacewind\Models;

use Spacewind\FilesDB\Model;

class PageData extends Model
{
    protected $title = 'Страница сайта';
    protected $db = '';
    protected $collection = 'pages';

    public function __construct()
    {
        global $path;
        $this->db = $path['main_configs'];
        parent::__construct();
    }
}
