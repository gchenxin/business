<?php

namespace App\Model;

use App\Traits\ModelTrait;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use ModelTrait;
    public $timestamps = false;
    protected $table = 'house_store as s';
    public $_table = 'house_store';

    public static function getStoreList(Int $uid,Int $pageSize,String $keyword){
        $where = [
            's.state' => 1,
            's.zjcom' => ZjCom::$zjcom
        ];
        $storeList = self::join("member as m","m.id","s.masterId")
            ->where($where);
        if($uid && ZjCom::$isManager) {
            //查询管理员的门店列表
            $storeInfo = BusinessManager::getManageInfo($uid);
            $stores = $storeInfo ? explode(',', $storeInfo['storeId']) : [];
            $addrArr = BusinessManager::getManageArea($uid);
            self::setAddrAndStore($storeList, ['s.addrid'=>$addrArr,'s.id'=>$stores]);
        }
        self::generateSearch($storeList, $keyword, ['s.name']);
		$list = $storeList->select("s.id","s.name",'s.addrid','s.address', 'm.username','m.nickname')->paginate($pageSize)->toArray();
		foreach($list['data'] as &$value){
			if($value['addrid']){
				$value['addrName'] = SiteArea::getFullNameById($value['addrid']);
			}
		}
		return $list;
    }

	public static function getStoreListByIds($ids){
		$ids = explode(',', trim($ids, ","));
		$result = self::whereIn('id', $ids)->select("s.id", "s.name")->get();
		if($result){
			$result = $result->toArray();
		}
		return $result;
	}
}
