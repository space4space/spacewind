<?php

namespace Spacewind;

class Util
{
    public static function translit($str)
    {
        $translit = array(
            'А' => 'a', 'Б' => 'b', 'В' => 'v', 'Г' => 'g', 'Д' => 'd', 'Е' => 'e', 'Ё' => 'e', 'Ж' => 'zh', 'З' => 'z', 'И' => 'i', 'Й' => 'y', 'К' => 'k', 'Л' => 'l', 'М' => 'm', 'Н' => 'n', 'О' => 'o', 'П' => 'p', 'Р' => 'r', 'С' => 's', 'Т' => 't', 'У' => 'u', 'Ф' => 'f', 'Х' => 'h', 'Ц' => 'ts', 'Ч' => 'ch', 'Ш' => 'sh', 'Щ' => 'shch', 'Ъ' => '', 'Ы' => 'y', 'Ь' => '', 'Э' => 'e', 'Ю' => 'yu', 'Я' => 'ya',
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e', 'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'shch', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
            'A' => 'a', 'B' => 'b', 'C' => 'c', 'D' => 'd', 'E' => 'e', 'F' => 'f', 'G' => 'g', 'H' => 'h', 'I' => 'i', 'J' => 'j', 'K' => 'k', 'L' => 'l', 'M' => 'm', 'N' => 'n', 'O' => 'o', 'P' => 'p', 'Q' => 'q', 'R' => 'r', 'S' => 's', 'T' => 't', 'U' => 'u', 'V' => 'v', 'W' => 'w', 'X' => 'x', 'Y' => 'y', 'Z' => 'z',
        );
        $result = strtr($str, $translit);
        $result = preg_replace('/[^a-zA-Z0-9_]/i', '-', $result);
        $result = preg_replace("/\-+/i", '-', $result);
        $result = preg_replace("/(^\-)|(\-$)/i", '', $result);

        return $result;
    }

    public static function rusDateHuman($value)
    {
        $time = strtotime($value);
        $param = 'd M Y';
        $MonthNames = array('Января', 'Февраля', 'Марта', 'Апреля', 'Мая', 'Июня', 'Июля', 'Августа', 'Сентября', 'Октября', 'Ноября', 'Декабря');
        if (strpos($param, 'M') === false) {
            return date($param, $time);
        } else {
            return date(str_replace('M', $MonthNames[date('n', $time) - 1], $param), $time);
        }
    }

    public static function arrayToXml($data, &$xml_data)
    {
        foreach ($data as $key => $value) {
            if (is_numeric($key)) {
                $key = 'item'.$key;
            }
            if (is_array($value)) {
                $subnode = $xml_data->addChild($key);
                self::arrayToXml($value, $subnode);
            } else {
                $xml_data->addChild("$key", htmlspecialchars("$value"));
            }
        }
    }

    public static function paginate($total, $number, $onpage)
    {
        $pagination = new stdClass();
        $lastpage = ceil($total / $onpage);
        if ($number > 1) {
            $pagination->previous = $number - 1;
        }
        if ($number < $lastpage) {
            $pagination->next = $number + 1;
        }
        if ($lastpage > 1 && $number != $lastpage) {
            $pagination->last = $lastpage;
        }

        $pagination->pages = [];
        if ($number > 4) {
            $pagination->pages[] = (object) ['dots' => ceil(($number - 1) / 2)];
        }
        for ($i = $number - 2; $i < $number; ++$i) {
            if ($i > 1) {
                $pagination->pages[] = (object) ['normal' => $i];
            }
        }
        $pagination->pages[] = (object) ['current' => $number];
        for ($i = $number + 1; $i < $number + 3; ++$i) {
            if ($i < $lastpage) {
                $pagination->pages[] = (object) ['normal' => $i];
            }
        }
        if ($number < $lastpage - 3) {
            $pagination->pages[] = (object) ['dots' => ceil(($lastpage - $number - 1) / 2) + $number + 1];
        }

        return $pagination;
    }

    public static function Get_VariableNameAsText($Variable = '', $Index = '')
    {
        $File = file(debug_backtrace()[0]['file']);

        try {
            if (!empty($Index) && !is_integer($Index)) {
                throw new UniT_Exception(UniT_Exception::UNIT_EXCEPTIONS_MAIN_PARAM, UniT_Exception::UNIT_EXCEPTIONS_PARAM_VALTYPE);
            }
        } catch (UniT_Exception $Exception) {
            $Exception->ExceptionWarning(__CLASS__, __FUNCTION__, $this->Get_Parameters(__CLASS__, __FUNCTION__)[1], gettype($Index), 'integer');
        }

        for ($Line = 1; $Line < count($File); ++$Line) {
            if ($Line == debug_backtrace()[0]['line'] - 1) {
                preg_match_all('/'.__FUNCTION__.'\((?<type>[a-z]{1,}\:{2}\${1}|\$this\x20{0,1}\-\>{1}\x20{0,1}|\${1})(?<variable>[A-Za-z0-9_]{1,})\x20{0,1}\,{0,1}\x20{0,1}(?<index>[0-9]{0,})\x20{0,}\)/', $File[$Line], $VariableName, PREG_SET_ORDER);

                if (empty($Index)) {
                    return $VariableName[0]['type'].$VariableName[0]['variable'];
                } else {
                    return $VariableName[$Index]['type'].$VariableName[$Index]['variable'];
                }
            }
        }
    }
}
