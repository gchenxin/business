<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class BusinessManager extends Model
{
    public $timestamps = false;
    protected $table = "business_manager as bm";
    public $_table = "business_manager";

    /**
     * 判断是否是管理员
     * @param $uid
     * @return mixed
     */
    public function judgeManageById($uid){
        return $this->where(['bm.userid'=>$uid, 'bm.state'=>1])
            ->join("house_zjuser as zj","zj.userid","bm.userid")
            ->join("house_zjcom as zc", "zj.zjcom", "zc.id")
            ->select("zc.id","bm.userid",'zc.state','zc.business_manage')->first();
    }

    /**
     * 获取管辖区域
     * @param Int $uid
     * @return array
     */
    public static function getManageArea(Int $uid){
        $manageAddr = self::where('bm.userid', $uid)->value('addrid');
        if(!$manageAddr){
            if(ZjCom::$isManager){
                return [];
            }
            $manageAddr = (new ZjCom())->getComServiceArea($uid);
        }
        $addrIds = [];
        if($manageAddr){
            (new SiteArea())->getAddrsById($manageAddr,$addrIds);
        }
        array_push($addrIds, $manageAddr);
        return $addrIds;
    }

    /**
     * 管理员列表
     * @param $pageSize
     * @return mixed
     */
    public function getManagerList($pageSize){
        $zjComId = ZjCom::$zjcom;
        $managerList = $this->join('member as m','m.id','bm.userid')
            ->where(['bm.zjcom'=>$zjComId,'m.state'=>1])
            ->select("m.nickname","m.username","m.phone","bm.addrid")
            ->paginate($pageSize);
        foreach($managerList as &$value){
            $value->manageArea = SiteArea::getFullNameById($value->addrid);
        }
        return $managerList;
    }

    public static function getManageInfo($mid){
        $zjComId = ZjCom::$zjcom;
        $info = self::where(['bm.zjcom'=>$zjComId,'userid'=>$mid])
            ->first()->toArray();
        return $info;
    }


}
