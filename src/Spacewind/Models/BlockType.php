<?php

namespace Spacewind\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spacewind\Traits\Logged;

class BlockType extends Model
{
    use SoftDeletes;
    use Logged;

    protected $title = 'Тип блока';
    protected $guarded = ['id'];
}
