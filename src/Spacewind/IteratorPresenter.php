<?php

namespace Spacewind;

class IteratorPresenter implements \IteratorAggregate
{
    private $values;

    public function __construct($values,$increment)
    {
        if (!is_array($values) && !$values instanceof \Traversable) {
            throw new \InvalidArgumentException('IteratorPresenter requires an array or Traversable object');
        }

        $this->values = $values;
        $this->increment = $increment;
    }

    public function getIterator()
    {
        $values = array();
        foreach ($this->values as $key => $val) {
            $values[$key] = array(
                'key'   => $key+$this->increment,
                'value' => $val,
                'first' => false,
                'last'  => false,
            );
        }

        $keys = array_keys($values);

        if (!empty($keys)) {
            $values[reset($keys)]['first'] = true;
            $values[end($keys)]['last']    = true;
        }

        return new \ArrayIterator($values);
    }
}