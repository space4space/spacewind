<?php

namespace Spacewind\Models;

use Illuminate\Database\Eloquent\Model;

class ChangeLog extends Model
{
    protected $guarded = ['id'];

    public function getDiffAttribute($value)
    {
        return json_decode($value);
    }

    public function setDiffAttribute($value)
    {
        $this->attributes['diff'] = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
