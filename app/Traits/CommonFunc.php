<?php

namespace App\Traits;

trait CommonFunc{

    /**
     * 返回不需要验证的接口列表
     * @return array
     */
    public function treeNodeGenerator(array $data){
        $refer = [];
        foreach($data as $key => $value){
            if(!$value['parentid'] && !isset($refer[$value['id']])){
                $refer[$value['id']] = &$data[$key];
            }else{
                $parent =& $refer[$value['parentid']];
                if(!isset($parent['childNode'][$value['id']])){
                    $parent['childNode'][$value['id']] = &$data[$key];
                }
            }
        }
        return $refer;
    }

}