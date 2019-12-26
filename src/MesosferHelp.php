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
        $json_encoded_string = str_replace('\"},{"', '"},{"', $json_encoded_string);
        $json_encoded_string = str_replace('[\{', '[{', $json_encoded_string);
        $json_encoded_string = str_replace('\"},"', '"},"', $json_encoded_string);
        $json_encoded_string = str_replace('\\\"', '"', $json_encoded_string);
        $json_encoded_string = str_replace('":\"', '":"', $json_encoded_string);
        $json_encoded_string = str_replace('\":\\"', '":"', $json_encoded_string);
        $json_encoded_string = str_replace(':\\"', ':"', $json_encoded_string);
        $json_encoded_string = str_replace('\\\"', '"', $json_encoded_string);
        $json_encoded_string = str_replace('":[{\"', '":[{"', $json_encoded_string);
        $json_encoded_string = str_replace('":\"', '":"', $json_encoded_string);
        $json_encoded_string = str_replace('\"},"', '"},"', $json_encoded_string);
        $json_encoded_string = str_replace('":{\"', '":{"', $json_encoded_string);
        $json_encoded_string = str_replace('{\"', '{"', $json_encoded_string);
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
            } elseif (isset($where->equalToBoolean)) {
                if ($where->equalToBoolean == 'True' || $where->equalToBoolean == 'true' || $where->equalToBoolean === true || $where->equalToBoolean == 1 || $where->equalToBoolean == '1') {
                    $query->equalTo($where->object, true);
                } elseif ($where->equalToBoolean == 'False' || $where->equalToBoolean == 'false' || $where->equalToBoolean === false || $where->equalToBoolean == 2 || $where->equalToBoolean == '2') {
                    $query->equalTo($where->object, false);
                }
            } elseif (isset($where->equalToNumber)) {
                $query->equalTo($where->object, $where->equalToNumber * 1);
            } elseif (isset($where->equalToPointer)) {
                $query->equalTo($where->object, new ParseObject($where->class, $where->objectId, true));
            } elseif (isset($where->notEqualTo)) {
                $query->notEqualTo($where->object, $where->notEqualTo);
            } elseif (isset($where->notEqualToBoolean)) {
                if ($where->notEqualToBoolean == 'True' || $where->notEqualToBoolean == 'true' || $where->notEqualToBoolean == true || $where->notEqualToBoolean == 1 || $where->notEqualToBoolean == '1') {
                    $query->notEqualTo($where->object, true);
                } elseif ($where->notEqualToBoolean == 'False' || $where->notEqualToBoolean == 'false' || $where->notEqualToBoolean == false || $where->notEqualToBoolean == 2 || $where->notEqualToBoolean == '2') {
                    $query->notEqualTo($where->object, false);
                }
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
                    $pointer = MesosferTools::needFormat('pointer', [$dat[2], $dat[3]]);
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
                    $point = new ParseGeoPoint(($dat[2]*1), ($dat[3]*1));
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
            return '"'.$dat[1].'":'.($dat[2] * 1);
        } elseif ($dat[0] == 'boolean') {
            if ($dat[2]=='true'||$dat[2]==true||$dat[2]=='True'||$dat[2]==1) {
                return '"'.$dat[1].'":true';
            } else {
                return '"'.$dat[1].'":false';
            }
        } elseif ($dat[0] == 'pointer') {
            return '"'.$dat[1].'":{
              "__type": "Pointer", "className": "'.$dat[3].'", "objectId": "'.$dat[2].'"
            }';
        } elseif ($dat[0]=='array') {
            return '"'.$dat[1].'":'.json_encode(array_values($dat[2]));
        } elseif ($dat[0]=='object') {
            return '"'.$dat[1].'":'.json_encode($dat[2]);
        } elseif ($dat[0] == 'geopoint') {
            return '"'.$dat[1].'":{
              "__type": "GeoPoint", "latitude": '.($dat[2] * 1).', "longitude": '.($dat[3] * 1).'
            }';
        }
    }

    public static function restConditional($options)
    {
        $query=[];
        foreach ($options as $where) {
            $where = MesosferTools::array2Json($where);
            if (isset($where->equalTo)) {
                if (isset($query[$where->object])) {
                    // Can't set
                } else {
                    $query[$where->object] = $where->equalTo;
                }
            } elseif (isset($where->notEqualTo)) {
                if (isset($query[$where->object])) {
                    $query[$where->object]['$ne'] = $where->notEqualTo;
                } else {
                    $query[$where->object] = [
                        '$ne' => $where->notEqualTo
                    ];
                }
            } elseif (isset($where->equalToBoolean)) {
                $bool = false;
                if ($where->equalToBoolean == 'True' || $where->equalToBoolean == 'true' || $where->equalToBoolean == true || $where->equalToBoolean == 1 || $where->equalToBoolean == '1') {
                    $bool = true;
                }
                
                if (isset($query[$where->object])) {
                    // Can't set
                } else {
                    $query[$where->object] = $bool;
                }
            } elseif (isset($where->equalToNumber)) {
                if (isset($query[$where->object])) {
                    // Can't set
                } else {
                    $query[$where->object] = $where->equalToNumber * 1;
                }
            } elseif (isset($where->equalToPointer)) {
                if (isset($query[$where->object])) {
                    // Can't set
                } else {
                    $pointer = MesosferTools::needFormat('pointer', [$where->objectId, $where->class]);
                    $query[$where->object] = $pointer;
                }
            } elseif (isset($where->notEqualToBoolean)) {
                $bool = false;
                if ($where->notEqualToBoolean == 'True' || $where->notEqualToBoolean == 'true' || $where->notEqualToBoolean == true || $where->notEqualToBoolean == 1 || $where->notEqualToBoolean == '1') {
                    $bool = true;
                }

                if (isset($query[$where->object])) {
                    $query[$where->object]['$ne'] = $bool;
                } else {
                    $query[$where->object] = [
                        '$ne' => $bool
                    ];
                }
            } elseif (isset($where->notEqualToNumber)) {
                if (isset($query[$where->object])) {
                    $query[$where->object]['$ne'] = $notEqualToNumber * 1;
                } else {
                    $query[$where->object] = [
                        '$ne' => $notEqualToNumber * 1
                    ];
                }
            } elseif (isset($where->notEqualToPointer)) {
                $pointer = MesosferTools::needFormat('pointer', [$where->objectId, $where->class]);
                if (isset($query[$where->object])) {
                    $query[$where->object]['$ne'] = $pointer;
                } else {
                    $query[$where->object] = [
                        '$ne' => $pointer
                    ];
                }
            } elseif (isset($where->containedIn)) {
                if (isset($query[$where->object])) {
                    $query[$where->object]['$in'] = $where->containedIn;
                } else {
                    $query[$where->object] = [
                        '$in' => $where->containedIn
                    ];
                }
            } elseif (isset($where->notContainedIn)) {
                if (isset($query[$where->object])) {
                    $query[$where->object]['$nin'] = $where->notContainedIn;
                } else {
                    $query[$where->object] = [
                        '$nin' => $where->notContainedIn
                    ];
                }
            } elseif (isset($where->greaterThan)) {
                if (isset($query[$where->object])) {
                    $query[$where->object]['$gt'] = $where->greaterThan * 1;
                } else {
                    $query[$where->object] = [
                        '$gt' => $where->greaterThan * 1
                    ];
                }
            } elseif (isset($where->lessThan)) {
                if (isset($query[$where->object])) {
                    $query[$where->object]['$lt'] = $where->lessThan * 1;
                } else {
                    $query[$where->object] = [
                        '$lt' => $where->lessThan  * 1
                    ];
                }
            } elseif (isset($where->greaterThanOrEqualTo)) {
                if (isset($query[$where->object])) {
                    $query[$where->object]['$gte'] = $where->greaterThanOrEqualTo * 1;
                } else {
                    $query[$where->object] = [
                        '$gte' => $where->greaterThanOrEqualTo * 1
                    ];
                }
            } elseif (isset($where->lessThanOrEqualTo)) {
                if (isset($query[$where->object])) {
                    $query[$where->object]['$lte'] = $where->lessThanOrEqualTo * 1;
                } else {
                    $query[$where->object] = [
                        '$lte' => $where->lessThanOrEqualTo  * 1
                    ];
                }
            }
        }

        $query = json_encode($query);
        return $query;
    }

    public static function loggingConditional($dataArray = [], $isPointer=[], $thisIsMaster=false, $writter='', $logAction='')
    {
        $data=[];
        array_push($data, ['boolean','master',$thisIsMaster]);
        array_push($data, ['pointer','actionBy',$writter,'_User']);
        array_push($data, ['string','actionStatus',$logAction]);

        foreach ($dataArray as $key => $datArr) {
            if ($key != 'createdAt' && $key != 'updatedAt'  && $key != 'ACL' && $key != 'log' && $key != 'lastAction' && $key != 'deleted') {
                if (is_string($datArr)) {
                    if ($key == 'objectId') {
                        array_push($data, ['string', 'fromObjectId', $datArr]);
                    } else {
                        array_push($data, ['string', $key, $datArr]);
                    }
                } elseif (is_numeric($datArr)) {
                    array_push($data, ['number', $key, $datArr]);
                } elseif (is_array($datArr)) {
                    $hasPointer = 0;
                    $hasFile = 0;
                    foreach ($isPointer as $pointer) {
                        if ($pointer[0] == $key) {
                            array_push($data, ['pointer',$key,$datArr['objectId'],$pointer[1]]);
                            $hasPointer = $hasPointer + 1;
                        }
                    }

                    if (!$hasPointer) {
                        if (isset($datArr['__type']) && $datArr['__type']=='GeoPoint') {
                            array_push($data, ['geopoint',$key,$datArr['latitude'],$datArr['longitude']]);
                        } elseif (isset($datArr['__type']) && $datArr['__type']=='File') {
                            $obj = '{
                                "__type":"File",
                                "url":"'.$datArr['url'].'",
                                "name":"'.$datArr['name'].'"
                            }';
                            array_push($data, ['object',$key,json_decode($obj)]);
                        } elseif (isset($datArr['date']) && isset($datArr['timezone_type']) && isset($datArr['timezone'])) {
                            array_push($data, ['date',$key,new DateTime($datArr['date'])]);
                        } else {
                            array_push($data, ['array',$key,$datArr]);
                        }
                    }
                } elseif (is_bool($datArr)) {
                    array_push($data, ['boolean',$key,$datArr]);
                }
            }
        }

        return $data;
    }

    public static function errorMessageHandler($output)
    {
        $fixOutput = '{
            "code":0,
            "message":""
        }';
        $fixOutput = json_decode($fixOutput);
        $fixOutput->code = isset($output->code)?$output->code:504;
        if ($fixOutput->code==504) {
            $fixOutput->message = 'Gateway Time-out';
        } elseif (isset($output->error)) {
            $fixOutput->message = $output->error;
        } elseif (isset($output->message)) {
            $fixOutput->message = $output->message;
        } else {
            $fixOutput->message = "";
        }

        return $fixOutput;
    }

    public static function getRestRelation($parent=["class"=>'',"objectId"=>'',"relColumn"=>'',"relClass"=>''])
    {
        $env = config('app.env');
        $protocol = config('mesosfer.' . $env . '.protocol');
        $host = config('mesosfer.' . $env . '.host');
        $port = config('mesosfer.' . $env . '.port');
        $subUrl = config('mesosfer.' . $env . '.subUrl');
        $headers = array(
            sprintf(config('mesosfer.' . $env . '.headerAppID') . ": %s", config('mesosfer.' . $env . '.appId')),
            sprintf(config('mesosfer.' . $env . '.headerRestKey') . ": %s", config('mesosfer.' . $env . '.restKey')),
            sprintf(config('mesosfer.' . $env . '.headerMasterKey') . ": %s", config('mesosfer.' . $env . '.masterKey'))
        );

        $queryIn=[];
        $queryIn['limit'] = 10000;
        
        $queryIn['where'] = '{"$relatedTo":{"object":{"__type":"Pointer","className":"_Role","objectId":"'.$parent['objectId'].'"},"key":"'.$parent['relColumn'].'"}}';

        $url = sprintf("%s://%s:%s/" . $subUrl . "/classes/%s?%s", $protocol, $host, $port, '_User', http_build_query($queryIn));
        
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 180);
        $output = json_decode(curl_exec($ch));
        $httpCode = curl_getinfo($ch);
        curl_close($ch);

        $response;
        if ($httpCode['http_code'] == 200) {
            if (isset($output->message)) {
                $output = MesosferHelp::errorMessageHandler($output);
                $response = [
                  "output" => [
                    "code" => $output->code,
                    "message" => $output->message
                  ],
                  "status" => false
                ];
            } else {
                if (isset($output->results)) {
                    $response = [
                      "output" => $output,
                      "status" => true
                    ];
                } else {
                    $response = [
                        "output" => $output,
                        "statusCode" => $httpCode,
                        "status" => false
                    ];
                }
            }
        } else {
            $output = MesosferHelp::errorMessageHandler($output);
            $response = [
                "output" => [
                    "code" => $output->code,
                    "message" => $output->message
                ],
                "statusCode" => $httpCode,
                "status" => false
            ];
        }
        $response = MesosferTools::array2Json($response);
        return $response;
    }
}
