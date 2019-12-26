<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;
use Illuminate\Routing\Exceptions\LoginTimeOutException;

class LoginRecord extends Model
{
    public $timestamps = false;
    protected $table = "business_login";
    public $_table = "business_login";

    protected $fillable = [
        'userid','loginIP','loginTime','keepExpire','state','reason'
    ];

    public function setLoginFailed($uid, $state = Response::API_SUCCESS, $lastLockTime = 0){
        $config = config('logic');
        $lastLockTime = $lastLockTime ?? 0;
        $ip = $_SERVER['X-Forwarded-For'] ?? $_SERVER['REMOTE_ADDR'];
        $data = [
            'userid'    =>  $uid,
            'loginIP'   =>  $ip,
            'loginTime' => time(),
            'state'     => $state == Response::API_SUCCESS ? 1 : 0
        ];
        if($state == Response::API_SUCCESS){
            $data['keepExpire'] = time() + $config['keepExpire'];
        }else{
            $data['reason'] = config('statetext')[$state];
        }
        self::create($data);
        if($state != Response::API_SUCCESS){
            //检查是否超过限制
            $info = $this->where(['userid'=>$uid, 'state'=>0])->where('loginTime','>=',$lastLockTime)->select('id')->get();
            if(count($info) >= $config['loginFailedTime']){
                //锁定账户
                app('App\User')->lockForLogin($uid);
            }
        }
        return true;
    }

    public function checkKeepLogin($uid, $clientIp){
        $loginInfo = $this->where(['userid' => $uid, 'loginIp' => $clientIp])->orderBy('loginTime', 'desc')->first();
        if(!$loginInfo || $loginInfo->keepExpire < time()){
            throw new LoginTimeOutException;
        }else{
            $this->where('id',$loginInfo->id)->update(['keepExpire'=>(time() + config('logic.keepExpire'))]);
        }
    }
}
