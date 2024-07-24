<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Route;

// adminapi子域名路由指定
Route::domain('localadminapi',function () {
    Route::rule('/:pathInfo', '/admin.:pathInfo')->pattern(['pathInfo'=>'[\w\.\/]+']);
    Route::miss(function() {
        return json([
            "code" => 0,
            "msg" => '路由不存在',
            "data" => new stdClass()
        ]);
    });
});