<?php

namespace App\Model;

use App\Traits\ModelTrait;
use Illuminate\Database\Eloquent\Model;

class ZjUser extends Model
{
    use ModelTrait;

    public $timestamps = false;
    protected $table = "house_zjuser as zj";
    public $_table = "house_zjuser";

    /**
     * 查询管理员区域下的经纪人列表
     * @param $mid
     */
    public function getZjListByManagerId(Int $uid,Int $pageSize, ?String $keyword = ''): ?Array{
        $addrIds = BusinessManager::getManageArea($uid);
        $storeInfo = BusinessManager::getManageInfo($uid);
        $orderBy = "zj.pubdate";
        $zjList = $this->join("member as m", "m.id", 'zj.userid')
            ->join(\DB::raw("(select id,zjcom from huoniao_house_zjuser where userid={$uid} Limit 1) as huoniao_myself"), 'zj.zjcom', 'myself.zjcom')
            ->join("house_zjcom as zc","zj.zjcom", "zc.id")
            ->select("zj.id",'m.userName','m.nickName',"zc.title",'zj.addr','zj.pubdate','zj.store',"zj.flag")
            ->where('zj.state',1)->where('zj.userid','!=',$uid);
        self::setAddrAndStore($zjList,['zj.addr'=>$addrIds,'zj.storeId'=>explode(',',$storeInfo['storeId'])]);
        self::generateSearch($zjList, $keyword, ['m.username','m.nickname','m.realname','m.phone']);
        $zjList = $zjList->orderBy($orderBy,'desc')->paginate($pageSize)->toArray();
        foreach($zjList['data'] as &$item){
            $item['addr'] = SiteArea::getFullNameById($item['addr']);
        }
        return $zjList;
    }

    public function getFlagAttribute($value){
        $name = ['未认证','已认证','认证失败'];
        return $name[$value];
    }

}
