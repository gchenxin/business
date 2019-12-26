<?php

namespace App\Http\Controllers;

use App\Model\BusinessManager;
use App\Model\Privilege;
use App\Model\UserPriv;
use App\Model\ZjCom;
use App\Traits\ModelTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Exceptions\NoPermissionException;
use Illuminate\Support\Facades\DB;

class SystemController extends Controller
{
    use ModelTrait;
    public static $apiAccess = [
        1 => "getManagerPrivilege",
        2 => 'update',
        4 => 'addManager',
        8 => 'delete'
    ];
    public function __construct(Request $request)
    {
        parent::__construct($request);
        //代码级别的强行限制主账号权限
//        $this->checkMainAccount();
    }

    public function getManagerList(BusinessManager $managerModel){
        $pageSize = empty($this->pageSize) ? 10 : $this->pageSize;
        //查询区域内的管理员列表
        return $managerModel->getManagerList($pageSize);
    }

    public function getPrivList(Privilege $privModel){
        $info = $privModel->getPrivList();
        return $this->treeNodeGenerator($info);
    }

    public function getGroupList(){
        $pageSize = empty($this->pageSize) ? 10 : $this->pageSize;
        return PrivGroup::getGroupList($pageSize);
    }

    public function getManagerPrivilege(){
        $list = app('App\Model\UserPriv')->getPrivById($this->zjId);
        if(!$list)  throw new NoPermissionException;
        return $list;
    }

    public function addManager(){
        if(empty($this->mId) || (empty($this->addrId) && empty($this->storeId)) || empty($this->gId)){
            self::exception(Response::INVALID_PARAMS);
        }
        $param = [
            'userid' => $this->mId,
            'zjcom' =>  ZjCom::$zjcom,
            'addrid' => $this->addrId,
            'storeId'   =>  empty($this->storeId) ? '':$this->storeId,
            'state' => empty($this->state) ? 0 : 1
        ];
        DB::beginTransaction();
        try{
            self::addItem(new BusinessManager, $param);
            (new UserPriv())->setManagerGroup($this->mId,$this->gId);
            DB::commit();
            return "OK";
        }catch(\Exception $e){
            DB::rollBack();
        }
        self::exception(Response::EXECUTE_FAILED);
    }

    public function modifyManager($mid){
        if(empty($this->addrId)){
            self::exception(Response::INVALID_PARAMS);
        }
        $param = [
            'addrid' => $this->addrId,
            'state' => empty($this->state) ? 0 : 1
        ];
        self::exception(Response::EXECUTE_FAILED);
        $result = self::modify(new BusinessManager, ['userid'=>$mid], $param);
        if(!$result){
            self::exception(Response::EXECUTE_FAILED);
        }
        return "OK";
    }

    public function deleteManager($mid){
        $result = self::remove(new BusinessManager, ['userid'=>$mid]);
        if(!$result){
            self::exception(Response::EXECUTE_FAILED);
        }
        return "OK";
    }

}
