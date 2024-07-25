<?php

namespace app\controller\admin;

// use app\model\admin\login\Log;
// use app\service\admin\AuthServiceFacade;
// use app\service\admin\UserServiceFacade;
// use app\service\Sms;
// use app\service\ConfServiceFacade;

// use app\validate\admin\user\Add;
// use app\validate\admin\user\Edit;
// use app\validate\admin\user\Login;
// use app\validate\admin\user\singleEdit;

use think\Exception;
use think\facade\Config;
use think\facade\Db;
use think\facade\Cache;

/**
 * 后台管理员控制器
 */
class AdminUser extends CommonController
{

    protected $model;//当前模型对象
    protected $noNeedLogin = [];
    protected $noNeedAuth = [];

    protected function _initialize()
    {
        $this->model = new \app\model\AdminUser();
    }

    public function pageData()
    {
        $where = [];
        $limit = $this->request->param('limit', 10);

        $data = $this->model->getPageData($where,$limit);

        return $this->success('数据获取成功', $data);
    }

}