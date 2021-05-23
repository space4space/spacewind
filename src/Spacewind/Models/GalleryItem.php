<?php

namespace Spacewind\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spacewind\Traits\Sequenced;
use Spacewind\Traits\Logged;

class GalleryItem extends Model
{
    use SoftDeletes;
    use Sequenced;
    use Logged;

    protected $title = 'Фото';
    protected $guarded = ['id'];

    public $relations = ['gallery', 'picture'];

    public function picture()
    {
        return $this->belongsTo('Spacewind\Models\Upload', 'picture_id');
    }

    public function gallery()
    {
        return $this->belongsTo('Spacewind\Models\Gallery');
    }
}
