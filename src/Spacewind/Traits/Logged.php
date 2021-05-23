<?php

namespace Spacewind\Traits;

trait Logged
{
    public $changed;

    public static function bootLogged()
    {
        static::updating(function ($model) {
            global $user;
            $changed = [];

            $log = new \Spacewind\Models\ChangeLog();
            $log->name = 'Изменение';
            if (isset($model->title)) {
                $log->title = $model->title;
            }
            $log->class = get_class($model);
            if (isset($user)) {
                $log->user_id = $user->id;
            }
            $log->object_id = $model->id;
            foreach ($model->getDirty() as $key => $value) {
                $original = $model->getOriginal($key);

                $changed[$key] = [
                    'old' => $original,
                    'new' => $value,
                ];
            }

            $log->diff = $changed;
            $log->save();
        });

        static::created(function ($model) {
            global $user;
            $changed = [];

            $log = new \Spacewind\Models\ChangeLog();
            $log->name = 'Создание';
            if (isset($model->title)) {
                $log->title = $model->title;
            }
            $log->class = get_class($model);
            if (isset($user->id)) {
                $log->user_id = $user->id;
            }
            $log->object_id = $model->id;
            $changed = $model->getDirty();

            if (isset($changed['id'])) {
                unset($changed['id']);
            }
            if (isset($changed['created_at'])) {
                unset($changed['created_at']);
            }
            if (isset($changed['updated_at'])) {
                unset($changed['updated_at']);
            }
            $log->diff = $changed;
            $log->save();
        });

        static::deleting(function ($model) {
            global $user;
            $changed = [];

            $log = new \Spacewind\Models\ChangeLog();
            $log->name = 'Удаление';
            if (isset($model->title)) {
                $log->title = $model->title;
            }
            $log->class = get_class($model);
            if (isset($user)) {
                $log->user_id = $user->id;
            }
            $log->object_id = $model->id;
            $changed = $model->getOriginal();

            if (isset($changed['id'])) {
                unset($changed['id']);
            }
            if (isset($changed['created_at'])) {
                unset($changed['created_at']);
            }
            if (isset($changed['updated_at'])) {
                unset($changed['updated_at']);
            }
            if (isset($changed['deleted_at'])) {
                unset($changed['deleted_at']);
            }
            $log->diff = $changed;
            $log->save();
        });
    }
}
