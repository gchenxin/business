<?php
//业务逻辑配置

return [
    //登录保持时间
    'keepExpire'    =>  1800,
    //登录失败锁定时间
    'loginLockDuration' =>  1800,
    //登录失败次数锁定
    'loginFailedTime'   => 5,

    'allow_origin'  =>  [
        'http://web.com',
        'http://www.gs.text:8081'
    ],
    'apiServerHostDebug' => 'http://test.fangruyu.net',
    'apiServerHostRelease' => 'https://fruyu.com',

];