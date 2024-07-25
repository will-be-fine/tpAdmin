<?php
declare (strict_types=1);

namespace app\traits;

/**
 * 所有接口success和error返回数据格式定义
 */
trait JsonReturn
{
    /**
     * 操作成功返回数据
     * @param string $msg
     * @param null $data
     * @return false|string|\think\response\Json
     */
    public function success($msg = '', $data = null)
    {
        $result = [
            'code' => 0,
            'msg'  => $msg,
            'time' => time(),
            'data' => $data ?? new \stdClass(),
        ];

        if (env('APP_DEBUG') && request()->param('debug')) {
            return json_encode($result, JSON_UNESCAPED_UNICODE);
        } else {
            return json($result);
        }
    }

    /**
     * 操作失败返回数据
     * @param string $msg
     * @param int $code 错误码，为0表示没有错误，为1表示常规错误，前端仅需提示msg，其他值有具体含义，比如10401为未登录，前端需要跳转至登录界面
     * @param array $data
     * @return \think\response\Json
     */
    public function error($msg = '', $code = 1, $data = null)
    {
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'time' => time(),
            'data' => $data ?? new \stdClass(),
        ];
        if (env('APP_DEBUG') && request()->param('debug')) {
            return json_encode($result, JSON_UNESCAPED_UNICODE);
        } else {
            return json($result);
        }
    }

    /**
     * 捕获异常后返回数据
     * @param $e
     * @return \think\response\Json
     */
    public function exceptionError($e){
        $data = [];
        if(env('APP_DEBUG')){
            $data = [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ];
        }
        return $this->error($e->getMessage(), 1, $data);
    }
}
