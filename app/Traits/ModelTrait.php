<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
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

    /**
     * 生成搜索
     * @param Builder $builder
     * @param $keyword
     * @param array $fields
     * @return Builder
     */
    public static function generateSearch(Builder &$builder, $keyword, Array $fields){
        if($builder && !empty($keyword) & !empty($fields)){
            $whereOr = [];
            foreach ($fields as $item){
                $whereOr[] = [$item, 'like', '%'.$keyword.'%','or'];
            }
        }else{
            return $builder;
        }
        $builder->where($whereOr);
    }

    /**
     * 生成地区和门店范围限制的sql
     * @param Builder $builder
     * @param array $array
     */
    public static function setAddrAndStore(Builder &$builder, Array $array){
        if($builder){
            $builder = $builder->where(function($query) use($array){
                $method = 'and';
                foreach ($array as $key => $item){
                    if($item){
                        $query->whereIn($key,$item,$method);
                        $method = "or";
                    }
                }
                return $query;
            });
        }
    }

    public function getItem($ModelName, $id){
        if(!strstr($ModelName, '\\')){
            $ModelName = "App\\Model\\" . $ModelName;
        }
        $model = app($ModelName);
		$result = $model->where('id',$id)->first();
		if($result){
			$result = $result->toArray();
		}else{
			$result = [];
		}
		return $result;
    }

}
