<?php

namespace Spacewind\Models;

use Spacewind\FilesDB\Model;

class PageDataAdm extends Model
{
    protected $title = 'Страница админки';
    protected $db = '';
    protected $collection = 'pages';

    public function __construct()
    {
        global $path;
        $this->db = $path['configs'];
        parent::__construct();
    }
}
