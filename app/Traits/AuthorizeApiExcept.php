<?php

namespace App\Traits;

trait AuthorizeApiExcept{

    /**
     * 返回不需要验证的接口列表
     * @return array
     */
    public function getNoAuthApi(){
        return [
            'test',
            'city',
            'user',
            'house'
        ];
    }
}