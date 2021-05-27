<?php

namespace Spacewind\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spacewind\Traits\Logged;

class Upload extends Model
{
    use SoftDeletes;
    use Logged;

    protected $title = 'Файл';
    protected $guarded = ['id'];
    public $relations = ['type', 'user'];

    public function type()
    {
        return $this->belongsTo('Spacewind\Models\UploadType', 'content_type', 'name')->withDefault(function ($upload_type) {
            $upload_type->icon = 'fa fa-file-o';
        });
    }

    public function user()
    {
        return $this->belongsTo('User');
    }

    public function preprocess($request)
    {
        global $uploaded;
        if (isset($uploaded)) {
            $request['file_size'] = $uploaded->size;
            $request['content_type'] = $uploaded->type;
        }

        return $request;
    }
}
