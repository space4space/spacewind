<?php

namespace Spacewind\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spacewind\Traits\Logged;

class Gallery extends Model
{
    use SoftDeletes;
    use Logged;

    protected $title = 'Галерея';
    protected $guarded = ['id'];

    public function items()
    {
        return $this->hasMany('Spacewind\Models\GalleryItem')->where('active', 1)->orderBy('sequence', 'DESC');
    }
}
