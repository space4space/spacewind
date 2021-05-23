<?php

namespace Spacewind\Models;

use Illuminate\Database\Eloquent\Model;

abstract class UserType extends Model
{
    protected $title = 'Тип пользователя';
    protected $fillable = ['id', 'name', 'str_id'];
    public $timestamps = false;

    public function users()
    {
        return $this->hasMany('User');
    }
}
