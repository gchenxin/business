<?php

namespace App\Http\Controllers;

use App\Model\ZjUser;
use App\Traits\SendRequest;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\NoPermissionException;

class MemberController extends Controller
{
    use SendRequest;
    public static $apiAccess = [
        1 => "getLocalZjList",
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
        if($this->privList){
            return $this->privList;
        }
        //没有权限
        throw new NoPermissionException;
    }

    public function getLocalZjList(ZjUser $zjUserModel){
        $pageSize = empty($this->pageSize) ? 10 : $this->pageSize;
        //查询区域内的经纪人列表
        return $zjUserModel->getZjListByManagerId((Int)$this->uid, (Int)$pageSize);
    }

    public function getUserInfo($uid){
//        return User::getUserInfoById($uid);
        $params = [
            'service' => 'member',
            'action' => 'detail',
            'uid'   =>  $uid
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

}
