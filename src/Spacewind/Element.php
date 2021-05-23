<?php

namespace Spacewind;

class Element
{
    public $name;
    public $properties;

    public function __construct($name)
    {
        global $path, $cfg;

        $this->name = $name;
        // $this->properties=$this->collection;
        // $this->properties=(object)$cfg->{$this::COLLECTION}->findOne(array('_id'=>$this->name));
        // if (!isset($this->properties->_id)){return false;}

        if (is_file($path['views'].'/assets/scripts/'.$this->name.'.js')) {
            $this->properties->inline_scripts = file_get_contents($path['views'].'/assets/scripts/'.$this->name.'.js');
        }

        if (is_file($path['views'].'/assets/styles/'.$this->name.'.css')) {
            $this->properties->inline_styles = file_get_contents($path['views'].'/assets/styles/'.$this->name.'.css');
        }

        $this->mapScriptNames();

        return $this;
    }

    public function __get($name)
    {
        if (!isset($this->properties->{$name})) {
            return null;
        }

        return $this->properties->{$name};
    }

    public function __isset($name)
    {
        return isset($this->properties->{$name});
    }

    public function arrayFlatten($array, &$newArray = array())
    {
        foreach ($array as $value) {
            if (is_array($value)) {
                $newArray = $this->arrayFlatten($value, $newArray);
            } else {
                if ($value != '') {
                    $newArray[] = $value;
                }
            }
        }

        return $newArray;
    }

    private function mapScriptNames()
    {
        global $cfg;

        $types = ['scripts', 'styles'];
        $map = $cfg->common->findOne(array('_id' => 'mapping-assets'));
        foreach ($types as $type) {
            if (isset($this->properties->{$type})) {
                $list = $this->properties->{$type};
                $this->properties->{$type} = $this->arrayFlatten(array_map(
                    function ($name) use ($map, $type) {
                        if (!empty($map[$type][$name])) {
                            return $map[$type][$name];
                        }
                    },
                    $list
                ));
            }
        }

        if (isset($this->properties->plugins)) {
            $list = $this->properties->plugins;
            $map = $cfg->common->findOne(array('_id' => 'mapping-plugins'));
            foreach ($types as $type) {
                $this->properties->plugins[$type] = $this->arrayFlatten(array_map(
                    function ($name) use ($map, $type) {
                        if (!empty($map[$type][$name])) {
                            return $map[$type][$name];
                        }
                    },
                    $list
                ));
            }
        }

        if (!isset($this->properties->header) && isset($this->properties->title)) {
            $this->properties->header = $this->properties->title;
        }
    }
}
