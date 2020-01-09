<?php

namespace App\Http\Controllers;

use App\Model\BusinessManager;
use App\Model\House;
use App\Model\ZjCom;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class HouseController extends Controller
{
    public static $apiAccess = [
        1 => "getHouseFlow,getDayReport",
        2 => 'update',
        4 => 'add',
        8 => 'delete'
    ];

    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    public function getHouseFlow(){
        if(empty($this->type)){
            self::exception(Response::INVALID_PARAMS);
        }
        $page = empty($this->page) ? 1 : $this->page;
        $pageSize = empty($this->pageSize) ? 10 : $this->pageSize;
        $zjcom = ZjCom::$zjcom;
        $areas = join(',',BusinessManager::getManageArea($this->uid));
        $info = BusinessManager::getManageInfo($this->uid);
        $houseScanInfo = DB::select("call houseFlowToDay('{$areas}',{$zjcom},'{$info['storeId']}','{$this->type}',{$page},{$pageSize})");

        return $houseScanInfo;
    }

    public function getDayReport(){
        if(empty($this->type)){
            self::exception(Response::INVALID_PARAMS);
        }
        $page = empty($this->page) ? 1 : $this->page;
        $pageSize = empty($this->pageSize) ? 10 : $this->pageSize;
        $zjcom = ZjCom::$zjcom;
        $areas = join(',',BusinessManager::getManageArea($this->uid));
        $info = BusinessManager::getManageInfo($this->uid);
        $houseDayReport = DB::select("call houseDayReport('{$this->type}','{$info['storeId']}','{$areas}',{$zjcom},{$page},{$pageSize})");

        return $houseDayReport;
    }


    public function getHouseList(House $houseModel, $type){
        if(!in_array($type, ['sale', 'zu', 'sp', 'xzl', 'cf'])){
            self::exception(Response::INVALID_PARAMS);
        }
        $keyword = empty($this->keyword) ? '' : $this->keyword;
        $pageSize = empty($this->pageSize) ? 10 : $this->pageSize;
     2   $areas = BusinessManager::getManageArea($this->uid);
        $storeId = "";
        if(ZjCom::$isManager){
            $info = BusinessManager::getManageInfo($this->uid);
            $storeId = $info['storeId'];
        }
        return $houseModel->getHouseList($type,$pageSize,$keyword,$areas,$storeId);
    }
}
