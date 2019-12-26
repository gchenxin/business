<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ZjUser extends Model
{
    public $timestamps = false;
    protected $table = "house_zjuser as zj";
    public $_table = "house_zjuser";

    /**
     * 查询管理员区域下的经纪人列表
     * @param $mid
     */
    public function getZjListByManagerId(Int $mid,Int $pageSize): ?Array{
        $addrIds = BusinessManager::getManageArea($mid);
        $orderBy = "zj.pubdate";
        $zjList = $this->join("member as m", "m.id", 'zj.userid')
            ->join(\DB::raw("(select id,zjcom from huoniao_house_zjuser where userid={$mid}) as huoniao_myself"), 'zj.zjcom', 'myself.zjcom')
            ->join("house_zjcom as zc","zj.zjcom", "zc.id")
            ->select("zj.id",'m.userName','m.nickName',"zc.title",'zj.addr','zj.pubdate','zj.store',"zj.flag")
            ->whereIn('zj.addr', $addrIds)
            ->where('zj.state',1)->orderBy($orderBy,'desc')->paginate($pageSize)->toArray();
        return $zjList;
    }

    public function getFlagAttribute($value){
        $name = ['未认证','已认证','认证失败'];
        return $name[$value];
    }

}
