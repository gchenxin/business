<?php

namespace App\Http\Controllers;

use App\Model\ZjCom;
use App\Traits\AuthorizeApiExcept;
use App\Traits\CommonFunc;
use App\Traits\ErrorTrait;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Response;
use Illuminate\Routing\Exceptions\NoPermissionException;
use Illuminate\Support\Facades\Route;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, AuthorizeApiExcept, CommonFunc, ErrorTrait;

    public function __construct(Request $request){
        $this->isAjax = $request->ajax();
        $this->route = (explode('/',Route::current()->uri()))[0];
        $this->method = $request->getMethod();
        $this->clientIp = $request->getClientIp();
        $param = $request->all();
        foreach ($param as $key => $value){
            $this->$key = $value;
        }
        if(empty($this->uid)){
            $this->uid = $request->route('uid');
        }
        if($this->route != "checkLogin" && $this->route != 'logout'){
            $this->init();
        }
    }

    /**
     * @return bool
     * @throws \ReflectionException
     */
    public function init(){
        if(!empty($this->uid)){
            //验证登录信息是否有效
            app('App\Model\LoginRecord')->checkKeepLogin($this->uid, $this->clientIp);
            //验证是否是合格的商户权限
            $isBusinessAdmin = app('App\Model\ZjCom')->checkBusiness($this->uid);
            if($isBusinessAdmin != Response::API_SUCCESS){
                return false;
            }
            $noAuthList = $this->getNoAuthApi();
            if(in_array($this->route,$noAuthList)){
                return ;
            }
            //验证用户权限
            if($this->route != "userPriv"){
                $privId = app('App\Model\Privilege')->getPidByName($this->route);
                $this->hasPermission = app('App\Model\UserPriv')->getPrivById($this->uid,$privId);
                //验证接口具体操作
                $reflector = new \ReflectionClass($this);
                if(!$reflector->hasProperty('apiAccess')){
                    throw new NoPermissionException;
                }
                if($reflector->hasMethod('checkAccess')){
                    $reflector->getMethod('checkAccess')->invoke($this);
                }
            }
        }else{
            self::exception(Response::INVALID_PARAMS);
        }
    }

    public function checkAccess(){
        $operation = $this->hasPermission;
        $apiName = Route::current()->getActionMethod();
        foreach(static::$apiAccess as $key => $item){
            if(($operation & $key) && strstr($item, $apiName)){
                return true;
            }
        }
		
		throw new NoPermissionException;
    }

    public function checkMainAccount(){
        if(ZjCom::$isManager){
            self::exception(Response::ONLY_MAINADMIN);
        }
    }

    public function test(){
        $areaInfo = $this->getItem('App\Model\BusinessManager',5);
        return $areaInfo;
    }
}
