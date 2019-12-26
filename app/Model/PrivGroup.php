<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PrivGroup extends Model
{
    public $timestamps = false;
    protected $table = "business_prigroup";
    public $_table = "business_prigroup";

    public static function getGroupList(Int $pageSize){
        return self::where('built_in',0)->select("id","name")->paginate($pageSize);
    }

}
