<?php

namespace library\token;

use think\facade\Config;

/**
 * Token基础类
 */
abstract class Driver
{
    protected $handler = null;
    protected $options = [];

    /**
     * 存储Token
     * @param string $token Token
     * @param int $user_id 会员ID
     * @param int $expire 过期时长,0表示无限,单位秒
     * @return bool
     */
    abstract function set($token, $user_id, $expire = 0);

    /**
     * 获取Token内的信息
     * @param string $token
     * @return  array
     */
    abstract function get($token);

    /**
     * 判断Token是否可用
     * @param string $token Token
     * @param int $user_id 会员ID
     * @return  boolean
     */
    abstract function check($token, $user_id);

    /**
     * 删除Token
     * @param string $token
     * @return  boolean
     */
    abstract function delete($token);

    /**
     * 删除指定用户的所有Token
     * @param int $user_id
     * @return  boolean
     */
    abstract function clear($user_id);

    /**
     * 返回句柄对象，可执行其它高级方法
     *
     * @access public
     * @return object
     */
    public function handler()
    {
        return $this->handler;
    }

    /**
     * 获取加密后的Token
     * @param string $token Token标识
     * @return string
     */
    protected function getEncryptedToken($token)
    {
        $tokenConfig = Config::get('token');
        return hash_hmac($tokenConfig['hashalgo'], $token, $tokenConfig['key']);
    }

    /**
     * 获取过期剩余时长
     * @param $expiretime
     * @return float|int|mixed
     */
    protected function getExpiredIn($expiretime)
    {
        return $expiretime ? max(0, $expiretime - time()) : 365 * 86400;
    }
}
