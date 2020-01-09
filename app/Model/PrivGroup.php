<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PrivGroup extends Model
{
    public $timestamps = false;
    protected $table = "business_prigroup";
    public $_table = "business_prigroup";

    public static function getGroupList(Int $pageSize){
        return self::where('built_in',0)->select("id","name")->paginate($pageSize)->toArray();
    }

    public static function parseGroupPrivileges(String $priv){
        $privList = explode(',', $priv);
        foreach ($privList as &$item) {
            $item = explode(":",$item);
            $item = ["id"=>$item[0], 'operation'=>$item[1]];
        }
        $privDetailList = Privilege::getPrivInfoByIds(array_column($privList, 'id'));
        $parentId = array_column($privDetailList, 'parentid');
        $privDetailList = array_merge(Privilege::getPrivInfoByIds($parentId),$privDetailList);
        return $privDetailList;
    }

}
