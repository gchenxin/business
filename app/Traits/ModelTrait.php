<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;

trait ModelTrait{

    /**
     * 插入数据
     * @return array
     */
    public static function addItem(Model $model,Array $params,bool $batch = false){
        if(!$params)    return false;
        if(!$batch){
            foreach($params as $key=>$value){
                $model->$key = $value;
            }
            return $model->setTable($model->_table)->save();
        }else{
            return $model->setTable($model->_table)->insert($params);
        }
    }

    public static function remove(Model $model,array $where){
        if(!$where) return false;
        return $model->setTable($model->_table)->where($where)->delete();
    }

    public static function modify(Model $model,array $where,Array $param){
        if(!$where) return false;
        return $model->setTable($model->_table)->where($where)->update($param);
    }
}