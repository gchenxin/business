<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class House extends Model
{
    public $timestamps = false;

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
}
