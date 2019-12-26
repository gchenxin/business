<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


//Route::middleware(['signed'])->group(function(){
Route::group([],function(){
//    Route::get("user/{id}_{name?}", "TestController@show")
//        ->where(['id' => '[0-9]+'])    //where定义参数的正则约束，?表示可选参数
//        ->name("test");

    Route::post("checkLogin", "Auth\\LoginController@checkLogin");
    Route::get("logout/{uid}", "Auth\\LoginController@logout");

    Route::get("user/{uid}", "MemberController@getUserInfo");
    Route::get("userPriv", "MemberController@getUserPrivList");
    Route::get("zjList", "MemberController@getLocalZjList");


    Route::get("getHouseFlow", "HouseController@getHouseFlow");


    Route::get('privList', "SystemController@getPrivList");
    Route::get('groupList', "SystemController@getGroupList");
    Route::get('managerPriv', "SystemController@getManagerPrivilege");

    Route::prefix("manager")->group(function(){
//        Route::get("/", "SystemController@getManagerList");
        Route::get('/', "SystemController@addManager");
        Route::delete('/{mid}', "SystemController@deleteManager");
        Route::put('/{mid}', "SystemController@modifyManager");
        Route::put('/grant/{mid}', "SystemController@modifyManager");
    });

    Route::get("areas/{cid?}", "CommonController@getCityArea");


});

