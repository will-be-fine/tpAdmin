<?php

namespace app\service\admin;

use think\Facade;

/**
 * 后台用户服务门面
 * @package app\service\admin
 * @method static mixed init($token) 初始化
 * @method static mixed getError() 获取错误信息
 * @method static mixed logout() 退出登录
 * @method static mixed getUserInfo() 获取登录用户信息
 * @method static mixed getAllowFields() 获取允许展示的字段
 * @method static mixed getUser() 获取User模型
 * @method static mixed isLogin() 获取登录状态
 * @method static mixed getToken() 获取token
 */
class UserServiceFacade extends Facade
{
    protected static function getFacadeClass()
    {
        return User::class;
    }
}
