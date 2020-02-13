<?php

namespace App\Http\Controllers;

use App\Model\BusinessManager;
use App\Model\PrivGroup;
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

    const INVALID_AREA = 1010;

    public static $apiAccess = [
        1 => "getManagerPrivilege,getManagerList,getPrivList,getGroupList",
        2 => 'modifyGroup,modifyManager',
        4 => 'addManager,addGroup',
        8 => 'deleteGroup,deleteManager'
    ];
    public function __construct(Request $request)
    {
        parent::__construct($request);
        //代码级别的强行限制主账号权限
//        $this->checkMainAccount();
    }

    public function getManagerList(BusinessManager $managerModel, $mId = 0){
        $pageSize = empty($this->pageSize) ? 10 : $this->pageSize;
        //查询区域内的管理员列表
        return $managerModel->getManagerList($pageSize, $mId);
    }

    public function getPrivList(Privilege $privModel){
        $info = $privModel->getPrivList();
        return $this->treeNodeGenerator($info);
    }

    public function getGroupList(){
        $pageSize = empty($this->pageSize) ? 10 : $this->pageSize;
        return PrivGroup::getGroupList($pageSize);
    }

    /**
     * 添加管理组
     */
    public function addGroup(){
        //参数检验
        if(empty($this->name) && empty($this->privStr)){
            self::exception(Response::INVALID_PARAMS);
        }
        //参数解析
        $privileges = $this->parsePrivileges();
        $param = [
            'name'  => $this->name,
            'pid'   =>  json_encode(array_values($privileges)),
            'built_in'  => 0
        ];
        if(self::addItem(new PrivGroup(),$param)){
            return ["OK"];
        }
        self::exception(Response::EXECUTE_FAILED);
    }

    public function modifyGroup($gid){
        if(empty($this->privStr)){
            self::exception(Response::INVALID_PARAMS);
        }
        $privileges = $this->parsePrivileges();
        if(self::modify(new PrivGroup(), ['id'=>$gid], ['pid'=>json_encode(array_values($privileges))])){
            return ['OK'];
        };
        self::exception(Response::EXECUTE_FAILED);
    }

    public function parsePrivileges(){
        $privileges = $this->treeNodeGenerator(PrivGroup::parseGroupPrivileges($this->privStr));
        array_map(function (&$item){
            if(isset($item['childNode'])){
                $item['childNode'] = array_values($item['childNode']);
            }
        }, $privileges);
        return $privileges;
    }

    public function deleteGroup($gid){
        DB::beginTransaction();
        try{
            self::remove(new PrivGroup(),['id'=>$gid]);
            self::remove(new UserPriv(),['gid'=>$gid]);
            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            self::exception(Response::EXECUTE_FAILED);
        }
        return ['OK'];
    }

    public function getManagerPrivilege(){
        $list = app('App\Model\UserPriv')->getPrivById($this->mId);
        if(!$list)  throw new NoPermissionException;
        return $list;
    }

    public function addManager(){
        if(empty($this->mId) || (empty($this->addrId) && empty($this->storeId)) || empty($this->gId)){
            self::exception(Response::INVALID_PARAMS);
        }
        //查询用户管辖区域
        $areaInfo = BusinessManager::getManageArea($this->uid);
        if(!in_array($this->addrId, $areaInfo)){
            self::exception(self::INVALID_AREA);
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

    public function modifyManager(UserPriv $userPrivModel,$mid){
        if((empty($this->addrId) && empty($this->storeId)) || empty($this->gId)){
            self::exception(Response::INVALID_PARAMS);
        }
        //查询用户管辖区域
        $areaInfo = BusinessManager::getManageArea($this->uid);
        if(!in_array($this->addrId, $areaInfo)){
            self::exception(self::INVALID_AREA);
        }
        $param = [
            'addrid' => empty($this->addrId) ? '' :$this->addrId,
            'storeId' => empty($this->storeId) ? '' : $this->storeId,
            'state' => empty($this->state) ? 0 : 1
        ];
        $managerInfo = $this->getItem("BusinessManager", $mid);
        DB::beginTransaction();
        try{
            self::modify(new BusinessManager, ['userid'=>$managerInfo['userid']], $param);
            $userPrivModel->modifyGroupForManager($managerInfo['userid'], $this->gId);
            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            self::exception(Response::EXECUTE_FAILED);
        }
        return "OK";
    }

    public function deleteManager($mid){
        $managerInfo = $this->getItem("BusinessManager", $mid);

        DB::transaction(function () use($managerInfo){
            self::remove(new BusinessManager, ['userid'=>$managerInfo['userid']]);
            self::remove(new UserPriv(),['userid'=>$managerInfo['userid']]);
        });
        return "OK";
    }

    public function grantToManager($mid){
        if(empty($this->privId)){
            self::exception(Response::INVALID_PARAMS);
        }
        $managerInfo = $this->getItem("BusinessManager", $mid);

    }

    public function revokeToManager($mid){
        if(empty($this->privId)){
            self::exception(Response::INVALID_PARAMS);
        }

    }

}
