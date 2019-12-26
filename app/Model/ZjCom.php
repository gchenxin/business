<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;

class ZjCom extends Model
{
    public $timestamps = false;
    protected $table = 'house_zjcom as zc';
    public $_table = 'house_zjcom';

    public static $zjcom;
    public static $isManager = false;

    public function checkBusiness($uid){
        $info = $this->where('zc.userid', $uid)->first();
        if(!$info){
            //检查是否是设置的管理账号
            $info = (new BusinessManager())->judgeManageById($uid);
            if(!$info)
                return Response::LOGIN_INVALID_BUSINESS;
            self::$isManager = true;
        }
        if(!$info->business_manage){
            return Response::LOGIN_SWITCH_MANAGEMENT;
        }
        if(!$info->state){
            return Response::LOGIN_ERR_STATE_BUSINESS;
        }
        self::$zjcom = $info->id;
        return Response::API_SUCCESS;
    }

    public function getComServiceArea($uid){
        return $this->where('zc.userid', $uid)->value('addr');
    }

    public function judgeMainAccount($uid){
        $zjomInfo = $this->where(['zc.state'=>1, 'zc.userid'=>$uid,'zc.id'=>self::$zjcom,'zc.business_manage'=>1])
            ->first();
        if($zjomInfo)
            return $zjomInfo->id;
    }

}