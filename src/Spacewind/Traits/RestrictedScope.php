<?php

namespace Spacewind\Traits;

use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class RestrictedScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        global $user;
        $role = $user->type->str_id ?? 'guest';

        if (!isset($role)) {
            throw new \Exception(get_class($model).': Не заданы права доступа.');
        }

        if (!isset($model->roles[$role])) {
            throw new \Exception(get_class($model).': Не заданы права доступа.');
        }

        if (($model->roles[$role] & (PERMISSION_READ_OWNED | PERMISSION_UPDATE_OWNED | PERMISSION_DELETE_OWNED)) && !isset($model->ownerProperty)) {
            throw new \Exception(get_class($model).': Нет свойства ownerProperty.');
        }

        if ($model->roles[$role] & PERMISSION_READ_OWNED) {
            return $builder->where($model->ownerProperty, $user->id);
        }

        //Функции проверок

        $checkCreate = function ($model) use ($user, $role) {
            if (!($model->roles[$role] & PERMISSION_CREATE)) {
                throw new \Exception(get_class($model).': Нет прав на создание.');
            }
            if ($model->roles[$role] & PERMISSION_CREATE_OWNED) {
                if ($model->{$model->ownerProperty} != $user->id) {
                    throw new \Exception(get_class($model).': Нет прав на запись объекта другого владельца.');
                }
            }
        };

        $checkRead = function ($model) use ($user, $role) {
            if (!($model->roles[$role] & PERMISSION_READ)) {
                throw new \Exception(get_class($model).': Нет прав на чтение.');
            }
        };

        $checkUpdate = function ($model) use ($user, $role) {
            if (!($model->roles[$role] & PERMISSION_UPDATE)) {
                throw new \Exception(get_class($model).': Нет прав на запись.');
            }
            if ($model->roles[$role] & PERMISSION_UPDATE_OWNED) {
                if ($model->getOriginal($model->ownerProperty) != $user->id) {
                    throw new \Exception(get_class($model).': Нет прав на изменение объекта другого владельца.');
                }
            }
        };

        $checkDelete = function ($model) use ($user, $role) {
            if (!($model->roles[$role] & PERMISSION_DELETE)) {
                throw new \Exception(get_class($model).': Нет прав на удаление.');
            }
            if ($model->roles[$role] & PERMISSION_DELETE_OWNED) {                
                if ($model->{$model->ownerProperty} != $user->id) {
                    throw new \Exception(get_class($model).': Нет прав на удаление объекта другого владельца.');
                }
            }
        };

        //Применяем функции для событий

        $model::creating($checkCreate);
        $model::retrieved($checkRead);
        $model::updating($checkUpdate);
        $model::saving($checkUpdate);
        $model::deleting($checkDelete);
    }
}
