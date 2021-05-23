<?php

namespace Spacewind\Traits;

trait Sequenced
{
    public function __construct(array $attributes = array())
    {
        if (!empty($this->fillable) && !in_array('sequence', $this->fillable)) {
            array_push($this->fillable, 'sequence');
        }

        parent::__construct($attributes);
    }

    public function save(array $options = array())
    {
        if (empty($this->sequence)) {
            $this->sequence = parent::max('sequence') + 1;
        }
        parent::save();
    }
}
