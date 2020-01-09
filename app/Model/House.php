<?php

namespace App\Model;

use App\Traits\ModelTrait;
use Illuminate\Database\Eloquent\Model;

class House extends Model
{
    use ModelTrait;
    public $timestamps = false;
    protected $table = "house_";

    public function areaHouseSqlGenerator(Int $uid): String{
        $addrIds = BusinessManager::getManageArea($uid);
        $addrIds = join(',',$addrIds);
        $zjcom = ZjCom::$zjcom;
        $subsql = <<<EOT
select s.id, 2 type
from huoniao_house_sale s
inner join huoniao_house_zjuser zj on s.userid = zj.id and zj.zjcom = {$zjcom}
where addrid in ({$addrIds})
union all
select s.id, 3 type
from huoniao_house_zu s
inner join huoniao_house_zjuser zj on s.userid = zj.id and zj.zjcom = {$zjcom}
where addrid in ({$addrIds})
union all
select s.id, 4 type
from huoniao_house_sp s
inner join huoniao_house_zjuser zj on s.userid = zj.id and zj.zjcom = {$zjcom}
where addrid in ({$addrIds})
union all
select s.id, 5 type
from huoniao_house_xzl s
inner join huoniao_house_zjuser zj on s.userid = zj.id and zj.zjcom = {$zjcom}
where addrid in ({$addrIds})
union all
select s.id, 6 type
from huoniao_house_cf s
inner join huoniao_house_zjuser zj on s.userid = zj.id and zj.zjcom = {$zjcom}
where addrid in ({$addrIds})
EOT;
        return $subsql;
    }

    public function getHouseList(String $type,Int $pageSize,String $keyword,Array $areas,String $storeId): ?Array{
        $this->table .= $type . " as s";
        $list = $this->setTable($this->table)->join("house_zjuser as zj",'zj.id','s.userid');
        self::generateSearch($list, $keyword, ['s.title','s.community','s.address']);
        self::setAddrAndStore($list,['zj.storeId'=>explode(',', $storeId),'s.addrid'=>$areas]);
        $list = $list->orderBy('s.pubdate', 'desc')->paginate($pageSize)->toArray();
        return $list;
    }

}
