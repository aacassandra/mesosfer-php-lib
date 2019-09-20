<?php

namespace Mesosfer;

use Parse\ParseClient;
use Parse\ParseException;
use Parse\ParseObject;
use Parse\ParseQuery;
use Parse\ParseUser;
use Parse\ParseCloud;
use Parse\ParseConfig;
use Parse\ParseSessionStorage;
use Mesosfer\MesosferHelp;
use Mesosfer\MesosferTools;
use Illuminate\Http\Request;

class MesosferAuth
{
    private $storageKey;

    public function __construct()
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
        $this->storageKey = config('mesosfer.'.$env.'.storageKey');
        ParseClient::initialize($appId, $restKey, $masterKey);
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        ParseClient::setStorage(new ParseSessionStorage());
        ParseClient::setServerURL($serverUrl, '/'.$subUrl);
    }


    /** Singup
    * $dataUser example format
    *'data' => [
    *  ['username',$userEmail],
    *  ['password',hash_hmac('sha256', $userEmail, $userEmail)],
    *  ['status','ADMIN']
    *]
    */
    public function signUp(Request $request, $dataUser = [], $data = [])
    {
        $user = new ParseUser();
        foreach ($dataUser as $dtUser) {
            $user->set($dtUser[0], $dtUser[1]);
        }

        try {
            $user->signUp();

            if (count($data)) {
                $request->session()->put('clientData', $data);
            }

            $response = [
              "output" => $this->getCurrentUser($request),
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

    // Data has saved on session after login has success
    // Data format like a below
    // $data = [
    // [$key,$value]
    // ...
    // ]
    public function signIn(Request $request, $username, $password, $data = [])
    {
        try {
            $user = ParseUser::logIn($username, $password);

            if (count($data)) {
                $request->session()->put('clientData', $data);
            }

            $response = [
              "output" => $this->getCurrentUser($request),
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

    public function getCurrentUser(Request $request, $refreshUser=false)
    {
        $currentUser = ParseUser::getCurrentUser();
        $sessionToken = $currentUser->getSessionToken();

        $currentUser = $currentUser->_encode();
        $currentUser = json_decode($currentUser, true);

        $newUserData;
        if ($refreshUser) {
            $newUserData = MesosferSdk::getObject('_User', $currentUser['objectId']);
            $newUserData= MesosferTools::json2Array($newUserData->output);
            $newUserData['sessionToken'] = $sessionToken;
            if ($request->session()->has('clientData')) {
                $clientData = $request->session()->get('clientData');
                foreach ($clientData as $key => $item) {
                    $newUserData[$item[0]] = $item[1];
                }
            }
            $currentUser = $newUserData;
        } else {
            $currentUser['sessionToken'] = $sessionToken;
            if ($request->session()->has('clientData')) {
                $clientData = $request->session()->get('clientData');
                foreach ($clientData as $key => $item) {
                    $currentUser[$item[0]] = $item[1];
                }
            }
        }

        foreach ($currentUser as $key => $cUser) {
            if (is_array($cUser)) {
                $cUser = MesosferTools::array2Json($cUser);
            }
            $request->session()->put($this->storageKey.'.'.$key, $cUser);
        }

        return MesosferTools::array2Json($currentUser);
    }

    public function signOut(Request $request)
    {
        if ($request->session()->has('clientData')) {
            $request->session()->forget('clientData');
        }
        $request->session()->forget($this->storageKey);
        ParseUser::logOut();
        return;
    }

    public function signInBecome(Request $request, $token='', $data=[])
    {
        try {
            $user = ParseUser::become($token);

            if (count($data)) {
                $request->session()->put('clientData', $data);
            }

            $response = [
              "output" => $this->getCurrentUser($request),
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

    public function getConfig($parameter = "")
    {
        $config = new ParseConfig();
        $value = $config->get($parameter);
        return $value;
    }

    public function setConfig($parameter = "", $value)
    {
        $config = new ParseConfig();
        $config->set($parameter, $value);
        $config->save();
        return;
    }
}
