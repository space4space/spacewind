<?php

namespace Spacewind\Models;

use Illuminate\Database\Eloquent\Model;

abstract class UserRememberHash extends Model
{
    protected $title = 'Хеш для изменения пароля';

    public function user()
    {
        return $this->belongsTo('User');
    }
}
