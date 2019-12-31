<?php

namespace App\Http\Controllers;

use App\Model\Store;
use App\Model\ZjUser;
use App\Traits\SendRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MemberController extends Controller
{
    use SendRequest;
    public static $apiAccess = [
        1 => "getStoreList",
        2 => 'update',
        4 => 'add',
        8 => 'delete'
    ];
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    /**
     * 获取用户权限列表
     * @return mixed
     */
    public function getUserPrivList(){
//        $uid = $this->uid;
//        if(isset($this->mId)){
//            $uid = $this->mId;
//        }
        $privList = app('App\Model\UserPriv')->getPrivById($this->uid);
        if($privList){
            return $privList;
        }
        //没有权限
        self::exception(Response::NO_DATA);
    }

    public function getLocalZjList(ZjUser $zjUserModel){
        $pageSize = empty($this->pageSize) ? 10 : $this->pageSize;
        $keyword = empty($this->param['keyword']) ? $this->param['keyword'] : '';
        //查询区域内的经纪人列表
        return $zjUserModel->getZjListByManagerId((Int)$this->uid, (Int)$pageSize, $keyword);
    }

    public function getUserInfo($uid){
//        return User::getUserInfoById($uid);
        $params = [
            'service' => 'member',
            'action' => 'detail',
            'id'   =>  $uid
        ];
        if(!empty($this->mid)){
            $params['uid'] = $this->mid;
        }
        $appMode = config('app.debug');
        if($appMode){
            $url = config('logic.apiServerHostDebug');
        }else{
            $url = config('logic.apiServerHostRelease');
        }
        $response = self::send($url . "/include/ajax.php",$params);
        return array_diff_key($response['info'],['name'=>0,'email'=>0,'phone'=>0,'idcard'=>0,'money'=>0]);
    }

//    public function modifyZjInfo(){
//        if(empty($this->zjId) || empty($this->storeId)){
//            self::exception(Response::INVALID_PARAMS);
//        }
//    }
    /**
     * 获取门店列表
     */
    public function getStoreList($uid){
        $pageSize = empty($this->pageSize) ? 10 : $this->pageSize;
        $keyword = isset($this->keyword) ? $this->keyword : '';
        return Store::getStoreList($uid, $pageSize, $keyword);
    }

}
