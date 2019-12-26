<?php

namespace App\Http\Controllers;

use App\Traits\SendRequest;
use Illuminate\Http\Request;

class CommonController extends Controller
{
    use SendRequest;
    public function __construct(Request $request)
    {
//        parent::__construct($request);
    }

    public function getCityArea($cid = 0){
        $params = [
            'service' => 'house',
            'action' => 'addr',
            'type'  => $cid,
            'hideSameCity' => 1
        ];
        $appMode = config('app.debug');
        if($appMode){
            $url = config('logic.apiServerHostDebug');
        }else{
            $url = config('logic.apiServerHostRelease');
        }
        $response = self::send($url . "/include/ajax.php",$params);
        return $response['info'];
    }
}
