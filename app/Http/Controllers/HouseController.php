<?php

namespace App\Http\Controllers;

use App\Model\BusinessManager;
use App\Model\ZjCom;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class HouseController extends Controller
{
    public static $apiAccess = [
        1 => "getHouseFlow",
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
        $info = BusinessManager::getManageStore($this->uid);
        $houseScanInfo = DB::select("call houseFlowToDay('{$areas}',{$zjcom},'{$info['storeId']}','{$this->type}',{$page},{$pageSize})");

        return $houseScanInfo;
    }
}
