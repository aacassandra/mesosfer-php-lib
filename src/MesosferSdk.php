<?php

namespace Mesosfer;

use Parse\ParseClient;
use Parse\ParseException;
use Parse\ParseObject;
use Parse\ParseQuery;
use Parse\ParseUser;
use Parse\ParseFile;
use Parse\ParseCloud;
use Parse\ParseConfig;
use Mesosfer\MesosferHelp;
use Mesosfer\MesosferTools;
use Mesosfer\MesosferAuth;

class MesosferSdk
{
    /**
     * Parse Initialize
     *
     * Initializes with the <APPLICATION_ID>, <REST_KEY>, and <MASTER_KEY>
     */
    public static function initialize()
    {
        $env = config('app.env');
        $appId = config('mesosfer.'.$env.'.appId');
        $restKey = config('mesosfer.'.$env.'.restKey');
        $masterKey = config('mesosfer.'.$env.'.masterKey');
        $host = config('mesosfer.'.$env.'.host');
        $port = config('mesosfer.'.$env.'.port');
        $subUrl = config('mesosfer.' . $env . '.subUrl');
        $protocol = config('mesosfer.'.$env.'.protocol');
        $serverUrl = $protocol."://".$host.":".$port;
        ParseClient::initialize($appId, $restKey, $masterKey);
        ParseClient::setServerURL($serverUrl, '/'.$subUrl);
    }

    /**
    * Parse Options Format Example
    * Include Supported: getAllObject & getObject
    * Where Supported: getAllObject
    *
    * Example:
    * $options = [
    *   "include" => [
    *       'participants','guests','pic','room.floor'
    *   ],
    *   "where" => [
    *     0 => [
    *       "object" => "objectId",
    *       "key" => "VpNpZD0005"
    *     ],
    *     1 => [
    *       "object" => "name",
    *       "key" => "John Tens"
    *     ],
    *     2 => [
    *       "object" => "game_scor"
    *       "lessThan" => [20]
    *     ]
    *     3 => [
    *       "object" => "game_scor"
    *       "lessThanOrEqualTo" => [15]
    *     ]
    *     4 => [
    *       "object" => "game_scor"
    *       "greaterThan" => [23]
    *     ]
    *     5 => [
    *       "object" => "game_scor"
    *       "greaterThanOrEqualTo" => [19]
    *     ]
    *     6 => [
    *       "object" => "createdAt"
    *       "greaterThanRelativeTime" => ['2 weeks ago']
    *     ]
    *     7 => [
    *       "object" => "createdAt"
    *       "lessThanRelativeTime" => ['in 1 day']
    *     ]
    *     8 => [
    *       "object" => "createdAt"
    *       "greaterThanOrEqualToRelativeTime" => ['1 year 2 weeks 30 days 2 hours 5 minutes 10 seconds ago']
    *     ]
    *     9 => [
    *       "object" => "createdAt"
    *       "lessThanOrEqualToRelativeTime" => ['now']
    *     ]
    *   ]
    * ];
    */
    public static function getAllObject($class, $options = [])
    {
        MesosferSdk::initialize();
        $query = new ParseQuery($class);

        //if where function has set
        if (isset($options['where'])) {
            MesosferHelp::conditional($query, $options['where']);
        } else {
            $query->notEqualTo("objectId", "");
        }

        //If include function has set
        if (isset($options['include'])) {
            foreach ($options['include'] as $include) {
                $query->includeKey($include);
            };
        }

        try {
            $query->limit(10000);
            $success = $query->find();
            $decode = MesosferHelp::responseDecode($success, 'array');

            if (count($success)==0) {
                $response = [
                  "output" => [
                    'code' => 404,
                    'message' => 'No data available'
                  ],
                  "status" => false
                ];
                $response = MesosferTools::array2Json($response);
                return $response;
            } else {
                if (count($success)==1) {
                    if (isset($options['need1Response'])) {
                        $response = [
                          "output" => $decode[0],
                          "status" => true
                        ];
                        $response = MesosferTools::array2Json($response);
                        return $response;
                    } else {
                        $response = [
                          "output" => $decode,
                          "status" => true
                        ];
                        $response = MesosferTools::array2Json($response);
                        return $response;
                    }
                } else {
                    $response = [
                      "output" => $decode,
                      "status" => true
                    ];
                    $response = MesosferTools::array2Json($response);
                    return $response;
                }
            }
        } catch (ParseException $error) {
            $response = [
              "output" => [
                'code' => $error->getCode(),
                'message' => $error->getMessage()
              ],
              "status" => false
            ];
            $response = MesosferTools::array2Json($response);
            return $response;
        }
    }

    public static function storeObject($class = "", $data = [], $options=[])
    {
        MesosferSdk::initialize();
        $object = new ParseObject($class);
        MesosferHelp::objectSet($object, $data);
        try {
            $object->save();

            $object = MesosferSdk::getObject($class, $object->getObjectId(), $options);
            $response;
            if ($object->status) {
                $response = [
                  "output" => $object->output,
                  "status" => true
                ];
            } else {
                $response = [
                  "output" => [
                    "objectId" => $object->getObjectId()
                  ],
                  "status" => true
                ];
            }

            $response = MesosferTools::array2Json($response);
            return $response;
        } catch (ParseException $error) {
            $response = [
              "output" => [
                'code' => $error->getCode(),
                'message' => $error->getMessage()
              ],
              "status" => false
            ];
            $response = MesosferTools::array2Json($response);
            return $response;
        }
    }

    /**
    * Parse Options Format Example
    * Include Supported: getAllObject & getObject
    *
    * Example:
    * $options = [
    *   "include" => [
    *       'participants','guests','pic','room.floor'
    *   ],
    * ];
    */
    public static function getObject($class = "", $id = "", $options = [])
    {
        MesosferSdk::initialize();
        $query = new ParseQuery($class);

        if (isset($options['where'])) {
            MesosferHelp::conditional($query, $options['where']);
        }

        if (isset($options['include'])) {
            foreach ($options['include'] as $include) {
                $query->includeKey($include);
            };
        }

        try {
            $success = $query->get($id);
            $decode = MesosferHelp::responseDecode($success, 'object');
            $response = [
              "output" => $decode,
              "status" => true
            ];
            $response = MesosferTools::array2Json($response);
            return $response;
        } catch (ParseException $error) {
            $response = [
              "output" => [
                'code' => $error->getCode(),
                'message' => $error->getMessage()
              ],
              "status" => false
            ];
            $response = MesosferTools::array2Json($response);
            return $response;
        }
    }


    /**
    * Parse Data Format Example
    * Object: Update
    *
    * Example:
    * $data = [
    *   0 =>[
    *     'pointer','floor',$request->adFloor,'Floor'
    *   ],
    *   1 => [
    *     'array','videoUrl',$request->adVideo
    *   ],
    *   2 => [
    *     'array','layoutSmall',$request->adSmall
    *   ],
    *   3 =>[
    *     'string','layoutMedium',$request->adMedium,
    *   ],
    *   4 =>[
    *     'array','layoutBig',$request->adBig
    *   ],
    *   5 =>[
    *    'boolean','actived',$request->actived
    *   ]
    *];
    */
    public static function updateObject($class = "", $id = "", $data = [], $options = [])
    {
        MesosferSdk::initialize();
        $query = new ParseQuery($class);

        try {
            $object = $query->get($id);
            MesosferHelp::objectSet($object, $data);
            $object->save();

            $object = MesosferSdk::getObject($class, $id, $options);
            $response;
            if ($object->status) {
                $response = [
                  "output" => $object->output,
                  "status" => true
                ];
            } else {
                $response = [
                  "output" => null,
                  "status" => true
                ];
            }

            $response = MesosferTools::array2Json($response);
            return $response;
        } catch (ParseException $error) {
            $response = [
              "output" => [
                'code' => $error->getCode(),
                'message' => $error->getMessage()
              ],
              "status" => false
            ];
            $response = MesosferTools::array2Json($response);
            return $response;
        }
    }

    public static function deleteField($class = "", $id = "", $field = "")
    {
        //
    }

    public static function deleteObject($class = "", $id = "")
    {
        MesosferSdk::initialize();
        $query = new ParseQuery($class);
        try {
            $object = $query->get($id);
            $object->destroy();
            $response = [
              "output" => null,
              "status" => true
            ];
            $response = MesosferTools::array2Json($response);
            return $response;
        } catch (ParseException $error) {
            $response = [
              "output" => [
                'code' => $error->getCode(),
                'message' => $error->getMessage()
              ],
              "status" => false
            ];
            $response = MesosferTools::array2Json($response);
            return $response;
        }
    }


    public static function deleteFile($array_mode=false, $url, $env_mode='')
    {
        if ($env_mode=='') {
            $env = config('app.env');
        } else {
            $env = $env_mode;
        }

        $appId = config('mesosfer.'.$env.'.appId');
        $headers = array(
          sprintf(config('mesosfer.' . $env . '.headerAppID') . ": %s", config('mesosfer.' . $env . '.appId')),
          sprintf(config('mesosfer.' . $env . '.headerMasterKey') . ": %s", config('mesosfer.' . $env . '.masterKey')),
          "Content-Type: application/json"
        );

        if ($array_mode) {
            foreach ($url as $item) {
                $ch = curl_init();
                $item = str_replace($appId."/", "", $item);
                curl_setopt($ch, CURLOPT_URL, $item);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                $output = '';
                $output = curl_exec($ch);
                $httpCode = curl_getinfo($ch);
                curl_close($ch);
            }
            return;
        } else {
            $ch = curl_init();
            $item = str_replace($appId."/", "", $url);
            curl_setopt($ch, CURLOPT_URL, $item);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);

            $output = '';
            $output = curl_exec($ch);
            $httpCode = curl_getinfo($ch);
            curl_close($ch);
            $response;
            if ($httpCode['http_code'] == 200) {
                if (isset($output->error)) {
                    $response = [
                      "output" => [
                        "code" => $output->code,
                        "message" => $output->error
                      ],
                      "status" => false
                    ];
                } else {
                    $response = [
                      "output" => $output,
                      "status" => true
                    ];
                }
            } else {
                $response = [
                  "output" => [
                    "requests" => $output,
                    "statusCode" => $httpCode
                  ],
                  "status" => false
                ];
            }
            $response = MesosferTools::array2Json($response);
            return $response;
        }
    }

    public static function uploadFile($file, $forceHttpsUrlFeedback = false)
    {
        $path = $file->getRealPath();
        $mime = $file->getMimeType();
        $nomeOriginal = $file->getClientOriginalName();
        $nomeCorrigido = preg_replace('/\\s+/', '', $nomeOriginal);
        $file = ParseFile::createFromFile($path, $nomeCorrigido, $mime);
        try {
            $file->save();
            $url = '';
            if ($forceHttpsUrlFeedback) {
                $url = str_replace("http://", "https://", $file->getUrl());
            } else {
                $url = $file->getUrl();
            }

            $response = [
              "output" => [
                '__type' => 'File',
                'url'    => $url,
                'name'   => $file->getName(),
              ],
              "status" => true
            ];
            $response = MesosferTools::array2Json($response);
            return $response;
        } catch (ParseException $error) {
            $response = [
              "output" => [
                'code' => $error->getCode(),
                'message' => $error->getMessage()
              ],
              "status" => false
            ];
            $response = MesosferTools::array2Json($response);
            return $response;
        }
    }

    public static function retrieveUser($id='')
    {
        $env = config('app.env');
        $currentUser = ParseUser::getCurrentUser();
        $sessionToken;
        if (isset($currentUser)) {
            $sessionToken = $currentUser->getSessionToken();
        } else {
            $storageKey = config('mesosfer.'.$env.'.storageKey');
            $sessionToken = session($storageKey.'.sessionToken');
        }

        $protocol = config('mesosfer.' . $env . '.protocol');
        $host = config('mesosfer.' . $env . '.host');
        $port = config('mesosfer.' . $env . '.port');
        $subUrl = config('mesosfer.' . $env . '.subUrl');
        $headers = array(
          sprintf(config('mesosfer.' . $env . '.headerAppID') . ": %s", config('mesosfer.' . $env . '.appId')),
          sprintf(config('mesosfer.' . $env . '.headerRestKey') . ": %s", config('mesosfer.' . $env . '.restKey')),
          sprintf(config('mesosfer.' . $env . '.headerSessionToken') . ": %s", $sessionToken),
          sprintf(config('mesosfer.' . $env . '.headerMasterKey') . ": %s", config('mesosfer.' . $env . '.masterKey')),
          "Content-Type: application/json",
        );

        $param = 'where={"objectId":"'.$id.'"}';
        $url = sprintf("%s://%s:%s/%s/users?%s", $protocol, $host, $port, $subUrl, $param);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $output = json_decode(curl_exec($ch));
        $httpCode = curl_getinfo($ch);
        curl_close($ch);
        $response;
        if ($httpCode['http_code'] == 200) {
            if (isset($output->error)) {
                $response = [
                  "output" => [
                      "code" => $output->code,
                      "message" => $output->error
                  ],
                  "status" => false
                ];
            } else {
                $response = [
                  "output" => $output->results[0],
                  "status" => true
                ];
            }
        } else {
            $response = [
              "output" => [
                "requests" => $output,
                "statusCode" => $httpCode
              ],
              "status" => false
            ];
        }
        $response = MesosferTools::array2Json($response);
        return $response;
    }

    public static function updateUsers($id, $data)
    {
        $env = config('app.env');
        $protocol = config('mesosfer.' . $env . '.protocol');
        $host = config('mesosfer.' . $env . '.host');
        $port = config('mesosfer.' . $env . '.port');
        $subUrl = config('mesosfer.' . $env . '.subUrl');
        $headers = array(
          sprintf(config('mesosfer.' . $env . '.headerAppID') . ": %s", config('mesosfer.' . $env . '.appId')),
          sprintf(config('mesosfer.' . $env . '.headerMasterKey') . ": %s", config('mesosfer.' . $env . '.masterKey')),
          "Content-Type: application/json",
        );


        $url = sprintf("%s://%s:%s/%s/users/%s", $protocol, $host, $port, $subUrl, $id);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $output = curl_exec($ch);
        $httpCode = curl_getinfo($ch);
        curl_close($ch);
        $output = json_decode($output);
        $response;
        if ($httpCode['http_code'] == 200) {
            if (isset($output->error)) {
                $response = [
                  "output" => [
                      "code" => $output->code,
                      "message" => $output->error
                  ],
                  "status" => false
                ];
            } else {
                $response = [
                  "output" => $output,
                  "status" => true
                ];
            }
        } else {
            $response = [
              "output" => [
                "requests" => $output,
                "statusCode" => $httpCode
              ],
              "status" => false
            ];
        }
        $response = MesosferTools::array2Json($response);
        return $response;
    }

    public static function getUserProfile($options)
    {
        $mesosfer = MesosferSdk::getAllObject('_User', $options);
        if ($mesosfer->status) {
            $response = [
              "output" => $mesosfer->output,
              "status" => true
            ];
            $response = MesosferTools::array2Json($response);
            return $response;
        } else {
            $response = [
              "output" => $mesosfer->output,
              "status" => false
            ];
            $response = MesosferTools::array2Json($response);
            return $response;
        }
    }

    public static function updateProfile($dataUser = [])
    {
        MesosferSdk::initialize();
        $currentUser = ParseUser::getCurrentUser();
        if ($currentUser) {
            MesosferHelp::objectSet($currentUser, $dataUser);
            try {
                $currentUser->save();
                $response = [
                  "output" => $currentUser,
                  "status" => true
                ];
                $response = MesosferTools::array2Json($response);
                return $response;
            } catch (ParseException $error) {
                $response = [
                  "output" => [
                    'code' => $error->getCode(),
                    'message' => $error->getMessage()
                  ],
                  "status" => false
                ];
                $response = MesosferTools::array2Json($response);
                return $response;
            }
        }
    }

    public static function getAggregation($class = "", $query = [])
    {
        //
    }

    public static function getConfig($parameter = "", $withSession=false)
    {
        $env = config('app.env');
        if (!$withSession) {
            // Only support single parameter
            MesosferSdk::initialize();
            $config = new ParseConfig();
            $value = $config->get($parameter);
            return $value;
        } else {
            // Has supported get all parameter
            $currentUser = ParseUser::getCurrentUser();
            $sessionToken;
            if (isset($currentUser)) {
                $sessionToken = $currentUser->getSessionToken();
            } else {
                $storageKey = config('mesosfer.'.$env.'.storageKey');
                $sessionToken = session($storageKey.'.sessionToken');
            }

            $protocol = config('mesosfer.' . $env . '.protocol');
            $host = config('mesosfer.' . $env . '.host');
            $port = config('mesosfer.' . $env . '.port');
            $subUrl = config('mesosfer.' . $env . '.subUrl');
            $headers = array(
                sprintf(config('mesosfer.' . $env . '.headerAppID') . ": %s", config('mesosfer.' . $env . '.appId')),
                sprintf(config('mesosfer.' . $env . '.headerRestKey') . ": %s", config('mesosfer.' . $env . '.restKey')),
                sprintf(config('mesosfer.' . $env . '.headerSessionToken') . ": %s", $sessionToken),
            );


            $url = sprintf("%s://%s:%s/%s/config", $protocol, $host, $port, $subUrl);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);

            $output = json_decode(curl_exec($ch));
            $httpCode = curl_getinfo($ch);
            curl_close($ch);
            $output = MesosferTools::json2Array($output->params);
            $value;
            if (isset($parameter) && $parameter != '') {
                foreach ($output as $key => $item) {
                    if ($key == $parameter) {
                        $value = $item;
                    }
                }
                $value = MesosferTools::array2Json($value);
            } else {
                $value = $output;
                $value = MesosferTools::array2Json($value);
            }

            return $value;
        }
    }

    public static function setConfig($parameter, $value)
    {
        if (is_array($parameter) or ($parameter instanceof Traversable)) {
            $env = config('app.env');
            $protocol = config('mesosfer.' . $env . '.protocol');
            $host = config('mesosfer.' . $env . '.host');
            $port = config('mesosfer.' . $env . '.port');
            $subUrl = config('mesosfer.' . $env . '.subUrl');
            $headers = array(
                      sprintf(config('mesosfer.' . $env . '.headerAppID') . ": %s", config('mesosfer.' . $env . '.appId')),
                      sprintf(config('mesosfer.' . $env . '.headerMasterKey') . ": %s", config('mesosfer.' . $env . '.masterKey')),
                      "Content-Type: application/json",
                  );
            $url = sprintf("%s://%s:%s/%s/config", $protocol, $host, $port, $subUrl);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($parameter));
            $output = json_decode(curl_exec($ch));
            $httpCode = curl_getinfo($ch);
            curl_close($ch);

            return $output;
        } else {
            MesosferSdk::initialize();
            $config = new ParseConfig();
            $config->set($parameter, $value);
            $config->save();
            return;
        }
    }

    /** $data = [
    *    [
    *      "method" => "PUT",
    *      "path" => "/2/classes/tesClass/QxjaTrWMay",
    *      "data" => [
    *        ["string","name","testing"],
    *        ["string","value","belajar"]
    *      ]
    *    ],
    *    [
    *      "method" => "PUT",
    *      "path" => "/2/classes/tesClass/8nB1NZuUzm",
    *      "data" => [
    *        ["string","name","testing"],
    *        ["pointer","user","s1XBQINJRF"]
    *      ]
    *    ]
    *];
    */
    public static function batchOperations($data, $withSession=false)
    {
        $data = MesosferTools::array2Json($data);

        $tmp = '';
        foreach ($data as $key => $item) {
            $method = strtoupper($item->method);
            if ($method == 'PUT' || $method == 'POST') {
                $tmp2 = '';
                foreach ($item->data as $key1 => $item1) {
                    $tmpC = MesosferHelp::batchConditional($item1);
                    if (!$key1) {
                        $tmp2 = $tmpC;
                    } else {
                        $tmp2 = $tmp2 .','.$tmpC;
                    }
                }

                $tmp1 = '{
                  "method": "'.$method.'",
                  "path": "'.$item->path.'",
                  "body": {
                    '.$tmp2.'
                  }
                }';

                if (!$key) {
                    $tmp = $tmp1;
                } else {
                    $tmp = $tmp .','. $tmp1;
                }
            } elseif ($method == 'delete') {
                $tmp1 = '{
                  "method": "'.$method.'",
                  "path": "'.$item->path.'"
                }';

                if (!$key) {
                    $tmp = $tmp1;
                } else {
                    $tmp = $tmp .','. $tmp1;
                }
            }
        }

        $tmp = '{"requests":[
        '.$tmp.'
        ]}';
        $env = config('app.env');
        $protocol = config('mesosfer.' . $env . '.protocol');
        $host = config('mesosfer.' . $env . '.host');
        $port = config('mesosfer.' . $env . '.port');
        $subUrl = config('mesosfer.' . $env . '.subUrl');
        $headers = array(
              sprintf(config('mesosfer.' . $env . '.headerAppID') . ": %s", config('mesosfer.' . $env . '.appId')),
              sprintf(config('mesosfer.' . $env . '.headerRestKey') . ": %s", config('mesosfer.' . $env . '.restKey')),
              "Content-Type: application/json"
          );

        if ($withSession) {
            $currentUser = ParseUser::getCurrentUser();
            $sessionToken;
            if (isset($currentUser)) {
                $sessionToken = $currentUser->getSessionToken();
            } else {
                $storageKey = config('mesosfer.'.$env.'.storageKey');
                $sessionToken = session($storageKey.'.sessionToken');
            }

            array_push($headers, sprintf(config('mesosfer.' . $env . '.headerSessionToken') . ": %s", $sessionToken));
        }

        $url = sprintf("%s://%s:%s/%s/batch", $protocol, $host, $port, $subUrl);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $tmp);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $output = curl_exec($ch);
        $httpCode = curl_getinfo($ch);
        curl_close($ch);

        $response = [
          "output" => [
            "requests" => json_decode($output),
            "statusCode" => $httpCode
          ],
          "status" => true
        ];
        $response = MesosferTools::array2Json($response);
        return $response;
    }
}
