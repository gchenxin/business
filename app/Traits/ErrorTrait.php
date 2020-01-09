<?php

namespace App\Traits;

trait ErrorTrait{

    /**
     * 返回不需要验证的接口列表
     * @return array
     */
    public static function exception($code){
        $errors = config('statetext');
        throw new \Exception($errors[$code], $code);
    }
}