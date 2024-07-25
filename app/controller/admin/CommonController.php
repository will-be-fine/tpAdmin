<?php

namespace app\controller\admin;
use app\controller\BaseController;
use app\middleware\admin\ActionLog;
use app\middleware\admin\Auth;
use app\service\admin\AuthServiceFacade;


class CommonController extends BaseController
{
    protected $noNeedLogin = [];//无需登录，也无需鉴权的方法名列表，支持*通配符定义所有方法
    protected $noNeedAuth = [];//需要登录，但是无需鉴权的方法名列表，支持*通配符定义所有方法
    protected $hasSoftDel = 0;//当前访问的模型是否有软删除功能
    protected $orderRule = ['id' => 'desc'];//默认排序规则

    /**
     * 中间件
     * @var array
     */
//    protected $middleware = [
//        Auth::class,
//        ActionLog::class,
//    ];


    /**
     * 基类初始化方法
     */
    protected function initialize()
    {
        //将无需登录的方法名数组设置到权限服务中
//        AuthServiceFacade::setNoNeedLogin($this->noNeedLogin);
//        //将无需鉴权的方法名数组设置到权限服务中
//        AuthServiceFacade::setNoNeedAuth($this->noNeedAuth);
        $this->_initialize();
    }

    //子类初始化
    protected function _initialize()
    {
    }
}