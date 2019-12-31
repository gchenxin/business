<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Privilege extends Model
{
    public $timestamps = false;
    protected $table = "business_privilege";
    public $_table = "business_privilege";

    public static $operation = [
        1 => "SELECT",
        2 => 'UPDATE',
        4 => 'ADD',
        8 => 'DELETE'
    ];

    public static function getPrivInfo($pid){
        return self::where('id', $pid)->first();
    }

    public static function getPrivInfoByIds(Array $ids){
        return self::whereIn('id', $ids)->get()->toArray();
    }

    public static function getPidByName($link){
        return self::where("link",$link)->value('id');
    }

    public static function getPrivList(){
        $list = self::where(['isOpen'=>1,'isNav'=>1])->orderBy('parentid')->get()->toArray();
        foreach($list as  &$value) {
            $value['operDesc'] = "";
            foreach (self::$operation as $key => $item) {
                if ($value['oper'] & $key) {
                    $value['operDesc'] .= $item . ",";
                }
            }
            $value['operDesc'] = trim($value['operDesc'], ',');
        }
        return $list;
    }
}
