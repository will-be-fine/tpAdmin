<?php

namespace app\controller\admin;

use app\Defineds;
use app\service\admin\UserServiceFacade;
use app\service\ConfServiceFacade;
use app\service\Sms;
use library\Http;
use library\Tree;
use think\facade\Cache;
use think\facade\Config;
use think\facade\Db;
use think\facade\Env;
use think\facade\Filesystem;
use think\File;

class Common extends CommonController
{
    protected $noNeedAuth = ['*'];
    protected $noNeedLogin = ['getLoginNeedCaptchaConf','getLoginNeedMobileConf','clearCache'];

    /**
     * 获取登录后台是否需要验证码的配置
     * 这个接口是后台登录界面使用的，给这个接口独立的访问权限，无需登录，无需鉴权
     */
    public function getLoginNeedCaptchaConf()
    {
        return $this->success('获取成功', ConfServiceFacade::get('system.basic.loginNeedCaptcha', 0));
    }

    /**
     * 获取登录后台是否需要手机短信的配置
     * 这个接口是后台登录界面使用的，给这个接口独立的访问权限，无需登录，无需鉴权
     */
    public function getLoginNeedMobileConf()
    {
        return $this->success('获取成功', ConfServiceFacade::get('system.basic.loginNeedMobile', 0));
    }

    //上传接口
    public function upload()
    {
        try {
            $uploadType = $this->request->param('upload_type', 'local');
            if (!in_array($uploadType, ['local', 'ali-oss', 'qiniu-kodo'])) {
                return $this->error($uploadType . '上传方式未定义');
            }
            $file = $this->request->file('laytpUploadFile'); // 获取上传的文件
            if (!$file) {
                return $this->error('上传失败，请选择需要上传的文件');
            }
            $fileExt = strtolower($file->getOriginalExtension());
            $uploadDomain = new UploadDomain();
            if (!$uploadDomain->check($file->getOriginalName(), $file->getSize(), $fileExt, $file->getMime())) {
                return $this->error($uploadDomain->getError());
            }
            $saveName = date("Ymd") . "/" . md5(uniqid(mt_rand())) . ".{$fileExt}";
            /**
             * 不能以斜杆开头
             *  - 因为OSS存储时，不允许以/开头
             */
            $uploadDir = $this->request->param('dir');
            $object = $uploadDir ? $uploadDir . '/' . $saveName : $saveName;//设置了上传目录的上传文件名
            $filePath = $object; //保存到lt_files中的path

            //如果上传的是图片，验证图片的宽和高
            $accept = $this->request->param('accept');
            if ($accept == "image") {
                $width = $this->request->param('width');
                $height = $this->request->param('height');
                if ($width || $height) {
                    $imageInfo = getimagesize($file->getFileInfo());
                    if (($width && $imageInfo[0] > $width) || ($height && $imageInfo[1] > $height)) {
                        return $this->error('上传失败，图片尺寸要求宽：' . $width . 'px，高：' . $height . 'px，实际上传文件[ ' . $file->getOriginalName() . ' ]的尺寸为宽' . $imageInfo[0] . 'px，高：' . $imageInfo[1] . 'px');
                    }
                }
            }

            $inputValue = "";
            //上传至七牛云
            if ($uploadType == 'qiniu-kodo') {
                if(ConfServiceFacade::get('qiniuKodo.conf.switch') != 1){
                    return $this->error('未开启七牛云KODO存储，请到七牛云KODO配置中开启');
                }
                $kodoConf = [
                    'accessKey' => ConfServiceFacade::get('qiniuKodo.conf.accessKey'),
                    'secretKey' => ConfServiceFacade::get('qiniuKodo.conf.secretKey'),
                    'bucket' => ConfServiceFacade::get('qiniuKodo.conf.bucket'),
                    'domain' => ConfServiceFacade::get('qiniuKodo.conf.domain'),
                ];
                $kodo = Kodo::instance();
                $kodoRes = $kodo->upload($file->getPathname(), $object, $kodoConf);
                if ($kodoRes) {
                    $inputValue = $kodoRes;
                } else {
                    return $this->error($kodo->getError());
                }
            }

            //上传至阿里云
            if ($uploadType == 'ali-oss') {
                if(ConfServiceFacade::get('system.aliOss.switch') != 1){
                    return $this->error('未开启阿里云OSS存储，请到阿里云OSS配置中开启');
                }
                $ossConf = [
                    'accessKeyID' => ConfServiceFacade::get('aliOss.conf.accessKeyID'),
                    'accessKeySecret' => ConfServiceFacade::get('aliOss.conf.accessKeySecret'),
                    'bucket' => ConfServiceFacade::get('aliOss.conf.bucket'),
                    'endpoint' => ConfServiceFacade::get('aliOss.conf.endpoint'),
                    'domain' => ConfServiceFacade::get('aliOss.conf.domain'),
                ];
                $oss = Oss::instance();
                $ossUploadRes = $oss->upload($file->getPathname(), $object, $ossConf);
                if ($ossUploadRes) {
                    $inputValue = $ossUploadRes;
                } else {
                    return $this->error($oss->getError());
                }
            }

            //本地上传
            if ($uploadType == 'local') {
                $uploadDir = ltrim('/', $uploadDir);
                $saveName = Filesystem::putFileAs('/' . $uploadDir, $file, '/' . $object);

                $filePath = $saveName;
                $staticDomain = Env::get('domain.static');
                if ($staticDomain) {
                    $inputValue = $staticDomain . '/storage/' . $saveName;
                } else {
                    $inputValue = request()->domain() . '/static/storage/' . $saveName;
                }

                //上传至图片服务器
                $imgBase64 = \app\Common::imgTransBase64($file);
                $res = Http::post(Config::get('service.apiUrl') . "?p=plugin&mod=service&c=upload_image",['content'=>$imgBase64,'save_name'=>$saveName,'url'=>$inputValue]);

                $resArr = json_decode($res,true);
                if($resArr['status']==1){
                    $inputValue = \app\Common::transferImg($resArr['data']['file_path']);
                }else{
                    return $this->error('图片上传失败,稍后再试');
                }
            }

            //将inputValue存入lt_files表中
//            $filesModel = new \app\model\Files();
//            $fileId = $filesModel->insertGetId([
//                'category_id' => 0,
//                'name' => $file->getOriginalName(),
//                'file_type' => $this->request->param('accept'),
//                'path' => $filePath,
//                'upload_type' => $uploadType,
//                'size' => $file->getSize(),
//                'ext' => $file->getExtension(),
//                'create_admin_user_id' => UserServiceFacade::getUser()->id,
//                'update_admin_user_id' => UserServiceFacade::getUser()->id,
//                'create_time' => date('Y-m-d H:i:s'),
//                'update_time' => date('Y-m-d H:i:s'),
//            ]);

            return $this->success('上传成功', [
//                'id' => $fileId,
                'path' => $inputValue,
                'name' => $file->getOriginalName(),
            ]);
        } catch (\Exception $e) {
            return $this->exceptionError($e);
        }
    }

    // 获取阿里云sts的临时凭证，目前仅用于客户端直接上传到oss前获取到临时凭证进行上传
    public function aliSts()
    {
        if(ConfServiceFacade::get('aliOss.sts.switch') != 1){
            return $this->error('阿里云OSS存储的STS方式，请到阿里云STS配置中开启');
        }
        $uploadDomain = new UploadDomain();
        $fileName = $this->request->param('name');
        $fileSize = $this->request->param('size');
        $fileExt = $this->request->param('ext');
        if (!$uploadDomain->check($fileName, $fileSize, $fileExt, '')) {
            return $this->error($uploadDomain->getError());
        }
        $stsConf = [
            'accessKeyID' => ConfServiceFacade::get('aliOss.sts.accessKeyID'),
            'accessKeySecret' => ConfServiceFacade::get('aliOss.sts.accessKeySecret'),
            'ARN' => ConfServiceFacade::get('aliOss.sts.ARN'),
            'endpoint' => ConfServiceFacade::get('aliOss.sts.endpoint'),
            'domain' => ConfServiceFacade::get('aliOss.sts.domain'),
            'bucket' => ConfServiceFacade::get('aliOss.sts.bucket'),
        ];
        $oss = Oss::instance();
        $sts = $oss->sts($stsConf);
        $file = new File('', false);

        if ($sts) {
            return $this->success('sts获取成功', [
                'sts' => $sts,
                'path' => str_replace('\\', '/', $file->hashName()) . $fileExt,
                'index' => $this->request->param('index'),
            ]);
        } else {
            return $this->error('sts获取失败，' . $oss->getError());
        }
    }

    // 获取kodo上传凭证token
    public function kodoToken()
    {
        if(ConfServiceFacade::get('qiniuKodo.conf.switch') != 1){
            return $this->error('未开启七牛云KODO存储，请到七牛云KODO配置中开启');
        }
        $uploadDomain = new UploadDomain();
        $fileName = $this->request->param('name');
        $fileSize = $this->request->param('size');
        $fileExt  = $this->request->param('ext');
        if (!$uploadDomain->check($fileName, $fileSize, $fileExt, '')) {
            return $this->error($uploadDomain->getError());
        }
        $kodoConf = [
            'accessKey' => ConfServiceFacade::get('qiniuKodo.conf.accessKey'),
            'secretKey' => ConfServiceFacade::get('qiniuKodo.conf.secretKey'),
            'domain'    => ConfServiceFacade::get('qiniuKodo.conf.domain'),
            'bucket'    => ConfServiceFacade::get('qiniuKodo.conf.bucket'),
        ];
        $kodo = Kodo::instance();
        $token = $kodo->token($kodoConf);
        $file = new File('', false);

        if ($token) {
            return $this->success('KodoToken获取成功', [
                'token' => $token,
                'domain' => ConfServiceFacade::get('qiniuKodo.conf.domain'),
                'bucket' => ConfServiceFacade::get('qiniuKodo.conf.bucket'),
                'path'  => str_replace('\\', '/', $file->hashName()) . $fileExt,
                'index' => $this->request->param('index'),
            ]);
        } else {
            return $this->error('KodoToken获取失败，' . $kodo->getError());
        }
    }

    //解锁屏幕
    function unLockScreen()
    {
        $password = $this->request->post('password');
        $userId = UserServiceFacade::getUser()->id;
        $passwordHash = User::where('id', '=', $userId)->value('password');
        if (!password_verify(md5($password), $passwordHash)) {
            return $this->error('解锁失败，密码错误');
        } else {
            return $this->success('解锁成功');
        }
    }

    function console(){
//        //待退货
//        $refundGoodsCount = Db::name('order_service')->where(['status'=>Defineds::SERVICE_STATUS_APPLY,'type'=>Defineds::SERVICE_TYPE_RETURN])->count();
//
//        //待退款
//        $refundMoneyCount = Db::name('order_service')->where(['status'=>Defineds::SERVICE_STATUS_APPLY,'type'=>Defineds::SERVICE_TYPE_REFUND])->count();

        //待发货
        $deliveryCount = Db::name('order')->where(['status'=>Defineds::ORDER_STATUS_DELIVER])->count();;

        //待上架
        $putshelvesCount = Db::name('v2_inventory')->where(' wait_stock>0 and (goods_id is null or goods_id=0 ) ')->count();


        //待入仓
        $warehouseInCount = Db::name('v2_putshelves_detail')->alias('pd')
            ->join('v2_putshelves p','p.id=pd.putshelves_id')
            ->where(['p.status'=>Defineds::PUTSHELVES_STATUS_WAIT])
            ->count();

        //待出仓
        $warehouseOutCount = Db::name('v2_outbound')->where(['status'=>Defineds::OUTBONUD_STATUS_WAIT])->count();

        //待出仓(抖音)
        $tiktokWarehouseOutCount = Db::name('v2_tiktok_order')->where(['status'=>Defineds::TIKTOC_ORDER_STATUS_WAIT])->count();

        //有待上架库存未上架
        $operateCount = Db::name('v2_inventory')->alias('i')
            ->join('goods g','i.goods_id=g.goods_id')
            ->where('i.wait_stock','>',0)
            ->where('g.status','=',Defineds::GOODS_STATUS_DOWN)
            ->count();

        //最新评论
//        $commentList = Db::name('comment')->where('content != "默认好评"')->order('id desc')->limit(10)->select()->toArray();
//        foreach ($commentList as &$row){
//            $row['adddate'] = date('Y-m-d H:i',$row['addtime']);
//        }
//        unset($row);

        //系统更新日志
        $systemUpdateLogList = Db::name('v2_system_update_log')->order('id desc')->limit(10)->select()->toArray();
        foreach ($systemUpdateLogList as &$row){
            $row['create_date'] = date('Y-m-d H:i',$row['create_time']);
        }
        unset($row);

        //操作日志
        $user = UserServiceFacade::getUser();

        $actionLogList = Db::name('v2_admin_action_log')
            ->alias('al')
            ->field('al.*,au.username,au.avatar')
            ->where("al.menu != ''")
            ->where(['al.admin_id'=>$user->id])
            ->leftJoin('v2_admin_user au',' au.id=al.admin_id')
            ->limit(10)
            ->order('al.id desc')
            ->select();


        $data = [
//            'refund_goods_count'    => $refundGoodsCount,
//            'refund_money_count'    => $refundMoneyCount,
            'delivery_count'        => $deliveryCount,
            'putshelves_count'      => $putshelvesCount,
            'warehouse_out_count'   => $warehouseOutCount,
            'warehouse_in_count'    => $warehouseInCount,
            'operate_count'         => $operateCount,
            'tiktok_warehouse_out_count'   => $tiktokWarehouseOutCount,

            'action_log_list'   => $actionLogList,
//            'comment_list'   => $commentList,
            'system_log_list'   => $systemUpdateLogList,
        ];
        return $this->success('获取成功',$data);
    }

    //商品分类
    public function goodsCategory(){
        $isRefresh = $this->request->param('is_refresh',0);
        $isTree = $this->request->param('is_tree');
        $commonModel = new \app\model\Common();
        $data = $commonModel->getGoodsCategoryList($isRefresh,$isTree);

        return $this->success('获取成功', $data);
    }

    //商品一级分类
    public function goodsCategoryLevel1(){
//        $isRefresh = $this->request->param('is_refresh',0);
//        $isTree = $this->request->param('is_tree');
//        $commonModel = new \app\model\Common();
//        $data = $commonModel->getGoodsCategoryList($isRefresh,$isTree);

        $data = Db::name('category')->field('id,name,pid')
            ->where(['pid'=>0,'status'=>1])
            ->order(['sort' => 'ASC','id'=>'ASC'])->select()->toArray();

        return $this->success('获取成功', $data);
    }

    //商品类型
    public function goodsType(){
        $isRefresh = $this->request->param('is_refresh',0);

        $commonModel = new \app\model\Common();
        $data = $commonModel->getGoodsTypeList($isRefresh);

        return $this->success('获取成功', $data);
    }

    //回收分类
    public function recycleCategory()
    {
        $isTree = $this->request->param('is_tree');
        $isRefresh = $this->request->param('is_refresh', 0);
        $commonModel = new \app\model\Common();
        $data = $commonModel->getRecycleCategoryList($isRefresh,$isTree);

        return $this->success('获取成功', $data);
    }

    //地址四级
    public function address(){
        $isRefresh = $this->request->param('is_refresh',0);
        $where = $this->buildSearchParams();


        $commonModel = new \app\model\Common();
        $data = $commonModel->getAddressList($isRefresh,$where);

        return $this->success('获取成功', $data);
    }

    //更新前端缓存
    public function clearCache(){
        set_time_limit(0);

        $options = [
            CURLOPT_CONNECTTIMEOUT => 60,
            CURLOPT_TIMEOUT => 60,
        ];

        $res = Http::post(Config::get('service.apiUrl') . "?p=plugin&mod=service&c=clear_cache",['act'=>'clear_diy_cache'],$options);

        return $this->success('操作成功');
    }

}
