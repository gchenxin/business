<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class SiteArea extends Model
{
    public $timestamps = false;
    protected $table = "site_area";
    public $_table = "site_area";

    public function getAddrsById($ids,array &$addrArr){
        if(!$ids)   return [];
        if(!is_array($ids)) $ids = explode(',', $ids);
        $subAreas = $this->whereIn('parentid', $ids)->pluck('id')->toArray();
        $addrArr = array_merge($addrArr,$subAreas);
        $this->getAddrsById($subAreas, $addrArr);
    }

    public static function getFullNameById($aid){
        //查询地区全称
        $info = self::where(['id'=>$aid])->select('id','typename','parentid')->first();
        if(!$info)  return "";
        $address = [$info->typename];
        while($info->parentid){
            $info = self::where(['id'=>$info->parentid])->select('id','typename','parentid')->first();
            if($info){
                array_push($address, $info->typename);
            }
        }
        $address = array_reverse($address);
        return join(' ', $address);
    }
}
