<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Http\Response;

class User extends Authenticatable
{
    use Notifiable;

    private $saltLength = 7;
    protected $table = "member";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password','paypwd','money','overide','phone','email','idcard'
    ];

    public $timestamps = false;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public static function getUserInfoById($id, $fields = "*"){
        if($id){
            if($fields != '*' && !is_array($fields)){
                return null;
            }
            return self::where(['id' => $id])->select($fields)->get()->toArray();
        }else{
            return null;
        }
    }

    public function getUserInfoByColumns(array $where, $fields = '*'){
        if(!$where) return null;
        if($fields != '*' && !is_array($fields)){
            return null;
        }
        return self::where($where)->select($fields)->get();
    }

    /**
     * @param $param
     * @return |null
     */
    public function checkLogin($param){
        $info = $this->where('username', $param['username'])->orWhere('phone',$param['username'])->first();
        if(!$info)  return Response::LOGIN_INVALID_BUSINESS;
        if(!$info->state)   return Response::LOGIN_ERR_STATE_BUSINESS;
        if($info->lockExpire && time() <= $info->lockExpire){
            return Response::LOGIN_AUTH_LOCK;
        }
        $loginRecordModel = new Model\LoginRecord;
        if($this->getSignHash($param['password'],$info->password) != $info->password){
            $loginRecordModel->setLoginFailed($info->id, Response::LOGIN_PASS_FAILED, $info->lockExpire);
            return Response::LOGIN_PASS_FAILED;
        }
        //检查是否是正常的商户账号
        $verifiedInfo = (new Model\ZjCom())->checkBusiness($info->id);
        if($verifiedInfo == Response::API_SUCCESS){
            $loginRecordModel->setLoginFailed($info->id, Response::API_SUCCESS);
            return ['state'=>$verifiedInfo, 'userid'=>$info->id];
        }
        return $verifiedInfo;
    }

    /**
     * 获取密码散列值
     * @param $string
     * @param null $salt
     * @return string
     */
    public function getSignHash($string, $salt = null){
        if(!$salt){
            $salt = substr(md5(time()), 0, $this->saltLength);
        }else{
            $salt = substr($salt, 0, $this->saltLength);
        }
        $md5 =  $salt . sha1($salt . $string);
        return $md5;
    }

    public function lockForLogin($uid){
        $expire = time() + config('logic.loginLockDuration');
        self::where('id', $uid)->update(['lockExpire'=>$expire]);
        return true;
    }
}
