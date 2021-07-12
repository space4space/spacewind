<?php

namespace Spacewind;

class MustacheHelpers
{
    private $engine;
    private $helpers = [];
    private $context;

    public function __construct($engine, $context)
    {
        $this->engine = $engine;
        $this->context = $context;
        $this->init();
    }

    public function add($helpersArray)
    {
        foreach ($helpersArray as $helper) {
            $this->engine->addHelper($helper, $this->helpers[$helper]);
        }
    }

    public function init()
    {
        $this->helpers['src'] = function ($value) {
            $value = (array) $value;

            if (!isset($value['image-hd'])) {
                $value['image-hd'] = $value['image'];
            }

            return 'src="'.$this->context['path']['assets'].$value['image'].'" data-src="'.$this->context['path']['assets'].$value['image'].'" data-src-retina="'.$this->context['path']['assets'].$value['image-hd'].'"';
        };

        $this->helpers['plain-json'] = function ($value) {
            if (isset($value)) {
                return json_encode($value);
            } else {
                return '';
            }
        };

        $this->helpers['money'] = function ($value) {
            $value = str_replace(',', '.', $value);
            if (isset($value)) {
                return number_format(floor($value), 0, '.', ' ').' руб. '.floor(fmod($value, 1) * 100).' коп.';
            } else {
                return '';
            }
        };

        $this->helpers['rus-date'] = function ($value) {
            return date_format(date_create_from_format('Y-m-d', $value), 'd-m-Y');
        };

        $this->helpers['rus-date-human'] = function ($value) {
            $time = strtotime($value);
            $param = 'd M Y';
            $MonthNames = array('Января', 'Февраля', 'Марта', 'Апреля', 'Мая', 'Июня', 'Июля', 'Августа', 'Сентября', 'Октября', 'Ноября', 'Декабря');
            if (strpos($param, 'M') === false) {
                return date($param, $time);
            } else {
                return date(str_replace('M', $MonthNames[date('n', $time) - 1], $param), $time);
            }
        };

        $this->helpers['substr'] = function ($value) {
            return substr(strip_tags($value), 0, 400);
        };

        $this->helpers['notags'] = function ($value) {
            return strip_tags($value);
        };

        $this->helpers['capitalize'] = function ($value) {
            return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
        };

        $this->helpers['css-min-link'] = function ($array) {
            if (!empty($array)){
                global $path;

                if (isset($array[0])) {
                    $value = $path['assets'].'/'.$array[0];
                } else {
                    $value = '';
                }

                for ($i = 1; $i < sizeof($array); ++$i) {
                    $value = $value.','.$path['assets'].'/'.$array[$i];
                }
                if ($value != '') {
                    return '<link rel="stylesheet" href="'.$path['assets'].'/min'.$value.'" />';
                } else {
                    return '';
                }
            }            
        };

        $this->helpers['js-min-link'] = function ($array) {
            if (!empty($array)){
                global $path;

                if (isset($array[0])) {
                    $value = $path['assets'].'/'.$array[0];
                } else {
                    $value = '';
                }

                for ($i = 1; $i < sizeof($array); ++$i) {
                    $value = $value.','.$path['assets'].'/'.$array[$i];
                }
                if ($value != '') {
                    return '<script src="'.$path['assets'].'/min'.$value.'"></script>';
                } else {
                    return '';
                }
            }
        };

        $this->helpers['out-vars'] = function ($obj) {
            $result = [];
            if (!empty($obj)) {
                foreach ((array) $obj as $key => $value) {
                    if (gettype($value) == 'string') {
                        $value = "'".$value."'";
                    }
                    array_push($result, (object) ['name' => $key, 'value' => $value]);
                }
            }

            return $result;
        };

        $this->helpers['form-by-name'] = function ($value) {
            global $path;
            $loader = new \Mustache_Loader_FilesystemLoader($path['views'].'/pages/partials/_shared/forms');

            if (isset($value['name'])) {
                $tpl = $loader->load($value['name']);

                return $this->engine->render($tpl, $this->context);
            } elseif (isset($value->name)) {
                $tpl = $loader->load($value->name);

                return $this->engine->render($tpl, $this->context);
            } else {
                return '';
            }
        };

        $this->helpers['generate-options'] = function ($value) {
            $str = '';

            
            $value=(object)$value;            

            if (!empty($value->null_value)) {
                $str .= '<option value="">----</option>';
            }

            if (isset($value->raw)) {
                foreach ($value->raw as $option) {
                    $option=(object)$option;
                    $str .= '<option value="'.$option->value.'">'.$option->text.'</option>';
                }
            }
            if (isset($value->source)) {
                foreach ($this->context[$value->source] as $option) {
                    if (isset($option->selected)) {
                        $str .= '<option selected value="'.$option->{$value->value}.'">'.$option->{$value->text}.'</option>';
                    } else {
                        $str .= '<option value="'.$option->{$value->value}.'">'.$option->{$value->text}.'</option>';
                    }
                }
            }

            return $str;
        };

        $this->helpers['generate-buttons'] = function ($value) {
            $str = '';

            if (isset($value['source'])) {
                foreach ($this->context[$value['source']] as $option) {
                    if (isset($option->selected)) {
                        $str .= '<label class="btn btn-complete btn-xs active">';
                        $str .= '<input type="radio" name="'.$value['name'].'" value="'.$option->{$value['value']}.'" checked>'.$option->{$value['text']};
                    } else {
                        $str .= '<label class="btn btn-complete btn-xs">';
                        $str .= '<input type="radio" name="'.$value['name'].'" value="'.$option->{$value['value']}.'">'.$option->{$value['text']};
                    }
                    $str .= '</label>';
                }
            }

            return $str;
        };
    }
}
