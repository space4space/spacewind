<?php

namespace Spacewind\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spacewind\Traits\Logged;

class Block extends Model
{
    use SoftDeletes;
    use Logged;

    protected $title = 'Блок';
    protected $guarded = ['id'];

    public function type()
    {
        return $this->belongsTo('Spacewind\Models\BlockType', 'block_type_id');
    }
}
