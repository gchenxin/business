<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;

class UserPriv extends Model
{
    public $timestamps = false;
    protected $table = "business_userpriv as up";
    public $_table = "business_userpriv";

    public function getPrivById($uid, $pid = null){
        $userPriv = self::leftJoin("business_prigroup as pg",'up.gid','pg.id')
            ->leftJoin("business_privilege as p", "up.pid", "p.id")
            ->select("up.id","up.userid","up.gid","up.pid","up.state","pg.pid as gourppriv","p.name",'p.link','p.state as pstate','up.oper','p.parentid','p.icon')
            ->where('up.userid',$uid)->get()->toArray();
        $privList = [];
        foreach ($userPriv as $key=>$item){
            if(!empty($item['pid'])){
                if($item['parentid']){
                    //查询上级信息
                    if(!isset($privList[$item['parentid']])){
                        $parentInfo = Privilege::getPrivInfo($item['parentid']);
                        if(!$parentInfo)    continue;
                        $privList[$parentInfo->id] = [
                            "id"=> $parentInfo->id,
                            "name" => $parentInfo->name,
                            "icon" => $parentInfo->icon,
                            "childNode" => []
                        ];
                    }
                }
                if($item['state'] != 1 || $item['pstate']!=1){
                    if($item['parentid']){
                        unset($privList[$item['parentid']]['childNode'][$item['pid']]);
                    }else{
                        unset($privList[$item['pid']]);
                    }
                }else{
                    $temp =& $privList;
                    if($item['parentid']){
                        $temp =& $privList[$item['parentid']]['childNode'];
                    }
                    if(!isset($temp[$item['pid']])){
                        $temp[$item['pid']] = [
                            "id"=>$item['pid'],
                            "name" => $item['name'],
                            "link" => $item['link'],
                            'icon' => $item['icon'],
                            "oper" => $item['oper']
                        ];
                    }
                }
            }elseif(!empty($item['gid'])){
                $privJson = json_decode($item['gourppriv'], true);
                foreach ($privJson as $privItem) {
                    if(!isset($privList[$privItem['id']])){
                        $privList[$privItem['id']] = [
                            "id"=>$privItem['id'],
                            "name" => $privItem['name'],
                            'icon' => $privItem['icon'],
                            'childNode' => []
                        ];
                    }
                    //处理一个经纪人属于多个管理组的情况
                    foreach ($privItem['childNode'] as $subItem){
                        if(!isset($privList[$privItem['id']]['childNode'][$subItem['id']])){
                            $privList[$privItem['id']]['childNode'][$subItem['id']] = $subItem;
                        }
                    }
                }
            }
        }
        if($pid && isset($privList[$pid]) && count($privList[$pid]['childNode']) != 0){
            return Response::API_SUCCESS;
        }
        //过滤无子菜单的一级菜单
        foreach($privList as $key=>$items){
            if($pid && $items && isset($items['childNode'][$pid])){
                return $items['childNode'][$pid]['oper'];
            }
            if(!$items || empty($items['childNode'])){
                unset($privList[$key]);
                continue;
            }
            $privList[$key]['childNode'] = array_values($items['childNode']);
        }
        return array_merge($privList);
    }

    public function setManagerGroup(Int $uid,$gid){
        $param = [];
        foreach(explode(',',$gid) as $item){
            $param[] = [
                'userid'    =>  $uid,
                'gid'   =>  $item,
                'state' =>  1
            ];
        }
        return $this->setTable($this->_table)->insert($param);
    }

    /**
     * 为管理员授权
     * @param Int $mId
     * @param array $privilege
     */
    public function grantPrivToManager(Int $mId, Array $privilege){
        $privilegeList = $this->getPrivById($mId);
        $grantList = Privilege::getPrivInfoByIds($privilege);
    }

    public function modifyGroupForManager($mid, $gid){
        //删除用户管理组
        $this->setTable($this->_table)->where('userid', $mid)->where('gid','!=','')->delete();
        $gidList = explode(',', $gid);
        $param = [];
        foreach ($gidList as $item) {
            $param[] = [
                'userid'    =>  $mid,
                'gid'   =>  $item,
                'state' =>  1
            ];
        }
        self::insert($param);
    }
}
