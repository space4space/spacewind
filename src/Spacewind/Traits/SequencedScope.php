<?php

namespace Spacewind\Traits;

use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class SequencedScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $order = 'desc';
        if (isset($model->sequence_order)) {
            $order = $model->sequence_order;
        }

        return $builder->orderBy($model->getTable().'.sequence', $order);
    }
}
