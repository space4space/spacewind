<?php

namespace Spacewind\Models;

use Illuminate\Database\Eloquent\Model;
use Spacewind\Traits\Logged;

class UploadType extends Model
{
    use Logged;

    protected $title = 'Тип файлов';
    protected $guarded = ['id'];
    public $timestamps = false;

    public function uploads()
    {
        return $this->hasMany('Upload', 'name', 'content_type');
    }
}
