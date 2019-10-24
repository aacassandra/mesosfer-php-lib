<?php

namespace Mesosfer;

use Parse\ParseFile;
use Parse\ParseGeoPoint;
use Parse\ParseObject;
use Mesosfer\MesosferTools;

class MesosferHelp
{
    public static function stripslashes($encoded)
    {
        $encoded = stripslashes(json_encode($encoded));
        $json_encoded_string = str_replace('{\"', '{"', $encoded);
        $json_encoded_string = str_replace('\":\"', '":"', $json_encoded_string);
        $json_encoded_string = str_replace('\",\"', '","', $json_encoded_string);
        $json_encoded_string = str_replace('\"},\"', '"},"', $json_encoded_string);
        $json_encoded_string = str_replace('\":{"', '":{"', $json_encoded_string);
        $json_encoded_string = str_replace(',\"', ',"', $json_encoded_string);
        $json_encoded_string = str_replace('\\\/', '/', $json_encoded_string);
        $json_encoded_string = str_replace('\\/', '/', $json_encoded_string);
        $json_encoded_string = str_replace('\\\"', '"', $json_encoded_string);
        $json_encoded_string = str_replace('\":', '":', $json_encoded_string);
        $json_encoded_string = str_replace('\"}"', '"}"', $json_encoded_string);
        $json_encoded_string = str_replace('["{', '[{', $json_encoded_string);
        $json_encoded_string = str_replace('}"]', '}]', $json_encoded_string);
        $json_encoded_string = str_replace('"}","{"', '"},{"', $json_encoded_string);
        $json_encoded_string = str_replace(':"{\"', ':{"', $json_encoded_string);
        $json_encoded_string = str_replace('":\"', '":"', $json_encoded_string);
        $json_encoded_string = str_replace('\",\"', '","', $json_encoded_string);
        $json_encoded_string = str_replace('"}","', '"},"', $json_encoded_string);
        $json_encoded_string = str_replace('":"{"', '":{"', $json_encoded_string);
        $json_encoded_string = str_replace('":{\"', '":{"', $json_encoded_string);
        $json_encoded_string = str_replace(',\"', ',"', $json_encoded_string);
        $json_encoded_string = str_replace('\"},\"', '"},"', $json_encoded_string);
        $json_encoded_string = str_replace('\"},"', '"},"', $json_encoded_string);
        $json_encoded_string = str_replace('}","{', '},{', $json_encoded_string);
        $json_encoded_string = str_replace('"{"', '{"', $json_encoded_string);
        $json_encoded_string = str_replace('"}"', '"}', $json_encoded_string);
        $json_encoded_string = str_replace('}"', '}', $json_encoded_string);
        $json_encoded_string = str_replace('"{', '{', $json_encoded_string);
        $json_encoded_string = str_replace('[\"', '["', $json_encoded_string);
        $json_encoded_string = str_replace('\"]', '"]', $json_encoded_string);
        $json_encoded_string = str_replace('":[\{\"', '":[{"', $json_encoded_string);
        $json_encoded_string = str_replace('\"}"],', '"}"],', $json_encoded_string);
        $json_encoded_string = str_replace('\"}],"', '"}],"', $json_encoded_string);
        $json_encoded_string = str_replace('"}"],"', '"}],"', $json_encoded_string);
        $json_encoded_string = str_replace('\"}}},"', '"}}},"', $json_encoded_string);
        return $json_encoded_string;
    }

    public static function responseDecode($data, $mode)
    {
        if ($mode=='array') {
            $encoded = [];

            // iterate over and store each encoded result
            foreach ($data as $result) {
                $encoded[] = $result->_encode();
            }

            $encoded = MesosferHelp::stripslashes($encoded);
            $decode = json_decode($encoded);
            return $decode;
        } elseif ($mode=='object') {
            $encoded = $data->_encode();
            $encoded = MesosferHelp::stripslashes($encoded);
            $decode = json_decode($encoded);
            return $decode;
        }
    }

    public static function conditional($query, $options)
    {
        foreach ($options as $where) {
            $where = MesosferTools::array2Json($where);
            //Get query from spesific string
            if (isset($where->equalTo)) {
                $query->equalTo($where->object, $where->equalTo);
            } elseif (isset($where->equalToNumber)) {
                $query->equalTo($where->object, $where->equalToNumber * 1);
            } elseif (isset($where->equalToPointer)) {
                $query->equalTo($where->object, new ParseObject($where->class, $where->objectId, true));
            } elseif (isset($where->notEqualTo)) {
                $query->notEqualTo($where->object, $where->notEqualTo);
            } elseif (isset($where->notEqualToNumber)) {
                $query->notEqualTo($where->object, $where->notEqualToNumber * 1);
            } elseif (isset($where->notEqualToPointer)) {
                $query->notEqualTo($where->object, new ParseObject($where->class, $where->objectId, true));
            } elseif (isset($where->containedIn)) {
                $query->containedIn($where->object, $where->containedIn);
            } elseif (isset($where->notContainedIn)) {
                $query->notContainedIn($where->object, $where->notContainedIn);
            //Get query from less than / greater than
            } elseif (isset($where->greaterThan)) {
                $query->greaterThan($where->object, $where->greaterThan);
            } elseif (isset($where->lessThan)) {
                $query->lessThan($where->object, $where->lessThan);
            } elseif (isset($where->greaterThanOrEqualTo)) {
                $query->greaterThanOrEqualTo($where->object, $where->greaterThanOrEqualTo * 1);
            } elseif (isset($where->lessThanOrEqualTo)) {
                $query->lessThanOrEqualTo($where->object, $where->lessThanOrEqualTo * 1);
            //Get query from less than / greater than of DateTime
            } elseif (isset($where->greaterThanRelativeTime)) {
                $query->greaterThanRelativeTime($where->object, $where->greaterThanRelativeTime);
            } elseif (isset($where->lessThanRelativeTime)) {
                $query->lessThanRelativeTime($where->object, $where->lessThanRelativeTime);
            } elseif (isset($where->greaterThanOrEqualToRelativeTime)) {
                $query->greaterThanOrEqualToRelativeTime($where->object, $where->greaterThanOrEqualToRelativeTime);
            } elseif (isset($where->lessThanOrEqualToRelativeTime)) {
                $query->lessThanOrEqualToRelativeTime($where->object, $where->lessThanOrEqualToRelativeTime);
            }
        };

        return $query;
    }

    public static function objectSet($object, $data)
    {
        foreach ($data as $dat) {
            if (isset($dat[3])) {
                if ($dat[0] == 'pointer') {
                    $pointer = '{
                      "__type": "Pointer",
                      "className": "'.$dat[3].'",
                      "objectId": "'.$dat[2].'"
                    }';
                    $pointer = json_decode($pointer);
                    $object->set($dat[1], $pointer);
                }
            } else {
                if ($dat[0] == 'string') {
                    $object->set($dat[1], $dat[2]);
                } elseif ($dat[0] == 'date') {
                    $object->set($dat[1], $dat[2]);
                } elseif ($dat[0] == 'number') {
                    $object->set($dat[1], ($dat[2] * 1));
                } elseif ($dat[0] == 'boolean') {
                    if (strpos($dat[2], 'false') !== false) {
                        $object->set($dat[1], false);
                    } elseif (strpos($dat[2], 'true') !== false) {
                        $object->set($dat[1], true);
                    } elseif ($dat[2] == true) {
                        $object->set($dat[1], true);
                    } elseif ($dat[2] == false) {
                        $object->set($dat[1], false);
                    }
                } elseif ($dat[0] == 'array') {
                    $object->setArray($dat[1], $dat[2]);
                } elseif ($dat[0] == 'object') {
                    $dat[2] = MesosferTools::json2Array($dat[2]);
                    $object->setAssociativeArray($dat[1], $dat[2]);
                } elseif ($dat[0] == 'image') {
                    $path = $dat[2]->getRealPath();
                    $mime = $dat[2]->getMimeType();
                    $nomeOriginal = $dat[2]->getClientOriginalName();
                    $nomeCorrigido = preg_replace('/\\s+/', '', $nomeOriginal);
                    $file = ParseFile::createFromFile($path, $nomeCorrigido, $mime);
                    $file->save();
                    $object->set($dat[1], $file);
                } elseif ($dat[0] == 'geopoint') {
                    $point = new ParseGeoPoint($dat[2][0], $dat[2][1]);
                    $object->set($dat[1], $point);
                } elseif ($dat[0] == 'delete') {
                    $object->delete($dat[1]);
                }
            }
        }

        return $object;
    }

    public static function batchConditional($dat = array())
    {
        if ($dat[0] == 'string') {
            return '"'.$dat[1].'":"'.$dat[2].'"';
        } elseif ($dat[0] == 'number') {
            return '"'.$dat[1].'":"'.($dat[2] * 1).'"';
        } elseif ($dat[0] == 'boolean') {
            if ($dat[2]=='true'||$dat[2]==true||$dat[2]=='True'||$dat[2]==1) {
                return '"'.$dat[1].'":true';
            } else {
                return '"'.$dat[1].'":false';
            }
        } elseif ($dat[0] == 'pointer') {
            return '"'.$dat[1].'":{
            "__type": "Pointer", "className": "_User", "objectId": "'.$dat[2].'"
          }';
        } elseif ($dat[0]=='array') {
            return '"'.$dat[1].'":'.json_encode($dat[2]);
        }
    }
}
