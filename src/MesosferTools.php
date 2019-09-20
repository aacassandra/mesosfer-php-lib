<?php

namespace Mesosfer;

use DateTime;

class MesosferTools
{
    public static function array2Json($arr)
    {
        $arr = json_encode($arr);
        $arr = json_decode($arr);
        return $arr;
    }

    public static function json2Array($json)
    {
        // $json = [$json];
        $json = json_encode($json);
        $json = json_decode($json, true);
        return $json;
    }

    public static function js2php($data)
    {
        $datas = [];
        foreach ($data as $item) {
            if ($item[0] == "date") {
                $item[2] = new DateTime($item[2]);
                array_push($datas, $item);
            } else {
                array_push($datas, $item);
            }
        }
        return $datas;
    }

    public static function datenow($format='', $utc=false)
    {
        $date = date($format);
        if (!$utc) {
            $date = date($format, strtotime($date)+7*60*60);
        } else {
            $date = date($format, strtotime($date));
        }
        return $date;
    }
}
