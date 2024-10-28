<?php

namespace app\controller\admin;

// use app\model\admin\login\Log;
// use app\service\admin\AuthServiceFacade;
// use app\service\admin\UserServiceFacade;
// use app\service\Sms;
// use app\service\ConfServiceFacade;

// use app\validate\admin\user\Add;
// use app\validate\admin\user\Edit;
use app\service\admin\AuthServiceFacade;
use app\service\admin\UserServiceFacade;
use app\service\ConfServiceFacade;
use app\validate\admin\user\Login;
use library\Random;
use think\facade\Cache;
use think\facade\Config;
use library\Token;

// use app\validate\admin\user\singleEdit;

//use app\validate\admin\user\Login;

/**
 * 后台管理员控制器
 */
class User extends CommonController
{

    protected $model;//当前模型对象
    protected $noNeedLogin = ['login', 'logout','sendMobileCode','mobileLogin'];
    protected $noNeedAuth = ['loginInfo', 'singleEdit','login'];

    protected function _initialize()
    {
        $this->model = new \app\model\User();
    }

    public function index()
    {
        $where = [];
        $limit = $this->request->param('limit', 10);

        $data = $this->model->getPageData($where,$limit);

        return $this->success('数据获取成功', $data);
    }

    public function login(){
        //获取表单提交数据
        $param = $this->request->post();

        //是否手机号登录
        if(ConfServiceFacade::get('system.basic.loginNeedMobile', 0)==1){
            $mobile = $param['mobile'];
            $code = $param['code'];

            if(empty($mobile)){
                return $this->error('手机号码不能为空');
            }
            if(empty($code)){
                return $this->error('验证码不能为空');
            }

            $sessionCode = Cache::get('admin_login_code_'.$mobile);
            if($sessionCode != $code){
                return $this->error('验证码错误');
            }

            //设置登录信息
            $loginUserInfo = \app\model\User::where('mobile', '=', $mobile)
                ->with(['avatar_file'])->field(UserServiceFacade::getAllowFields())->findOrEmpty();


            if(!$loginUserInfo->id){
                return $this->error('手机号不存在');
            }
        }else{
            //验证表单提交
            $validate = new Login();
            $validate->check($param);

            if (!$validate->check($param)) {
                $param['password'] = '******';//登录失败也不记录用户密码
//                Log::create([
//                    'login_status'   => 2,
//                    'admin_id'       => 0,
//                    'request_body'   => json_encode($param),
//                    'request_header' => json_encode($this->request->header()),
//                    'ip'             => $this->request->ip(),
//                    'create_time'    => date('Y-m-d H:i:s'),
//                ]);
                return $this->error($validate->getError());
            }

            //设置登录信息
            $loginUserInfo = \app\model\User::where('username', '=', $param['username'])
                ->field(UserServiceFacade::getAllowFields())->findOrEmpty();
        }


        $loginUserInfo->login_time = date('Y-m-d H:i:s');
        $loginUserInfo->login_ip   = $this->request->ip();
        $loginUserInfo->save();
        $userId = $loginUserInfo['id'];
        $token   = Random::uuid();
        $loginUserInfo['token'] = $token;
        Token::set($token, $userId, 10 * 60 * 60); //登录缓存10h

        $param['password'] = '******';//登录成功不记录用户密码
//        Log::create([
//            'login_status'   => 1,
//            'admin_id'       => $userId,
//            'request_body'   => json_encode($param),
//            'request_header' => json_encode($this->request->header()),
//            'ip'             => $this->request->ip(),
//            'create_time'    => date('Y-m-d H:i:s'),
//        ]);

        $authList = AuthServiceFacade::getAuthList($userId);
        return $this->success('登录成功', [
            'user'=>$loginUserInfo,
            'authList'=>$authList,
            'pluginConf'=>Config::get('plugin'),
        ]);
    }

    public function loginInfo(){

       $loginUserInfo = UserServiceFacade::getUserInfo();
       $authList = AuthServiceFacade::getAuthList($loginUserInfo['id']);

        return $this->success('获取成功', [
            'user'=>$loginUserInfo,
            'authList'=>$authList,
            'pluginConf'=>['editor'=>['ueditor', 'meditor']],
        ]);

    }

}