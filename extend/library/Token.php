<?php

namespace library;

use library\token\Driver;
use think\facade\Config;
use think\facade\Log;

/**
 * Token操作类
 */
class Token
{
    /**
     * @var array Token的实例
     */
    public static $instance = [];

    /**
     * @var object 操作句柄
     */
    public static $handler;

    /**
     * 连接Token驱动
     * @access public
     * @param array $options 配置数组
     * @param bool|string $name Token连接标识 true 强制重新连接
     * @return Driver
     */
    public static function connect(array $options = [], $name = false)
    {
        $type = !empty($options['type']) ? $options['type'] : 'File';

        if (false === $name) {
            $name = md5(serialize($options));
        }

        if (true === $name || !isset(self::$instance[$name])) {
            $class = false === strpos($type, '\\') ?
                'library\\token\\driver\\' . ucwords($type) :
                $type;

            // 记录初始化信息
            Config::get('app.app_debug') && Log::record('[ TOKEN ] INIT ' . $type, 'info');

            if (true === $name) {
                return new $class($options);
            }

            self::$instance[$name] = new $class($options);
        }

        return self::$instance[$name];
    }

    /**
     * 自动初始化Token
     * @access public
     * @param array $options 配置数组
     * @return Driver
     */
    public static function init(array $options = [])
    {
        if (is_null(self::$handler)) {
            if (empty($options) && 'complex' == Config::get('token.type')) {
                $default = Config::get('token.default');
                // 获取默认Token配置，并连接
                $options = Config::get('token.' . $default['type']) ?: $default;
            } elseif (empty($options)) {
                $options = Config::get('token');
            }
            self::$handler = self::connect($options);
        }

        return self::$handler;
    }

    /**
     * 判断Token是否可用(check别名)
     * @access public
     * @param string $token Token标识
     * @param int $userId 用户ID
     * @return bool
     */
    public static function has($token, $userId)
    {
        return self::check($token, $userId);
    }

    /**
     * 判断Token是否可用
     * @param string $token Token标识
     * @param int $userId 用户ID
     * @return bool
     */
    public static function check($token, $userId)
    {
        return self::init()->check($token, $userId);
    }

    /**
     * 读取Token
     * @access public
     * @param string $token Token标识
     * @param mixed $default 默认值
     * @return mixed
     */
    public static function get($token, $default = false)
    {
        return self::init()->get($token, $default);
    }

    /**
     * 写入Token
     * @access public
     * @param string $token Token标识
     * @param mixed $userId 存储数据
     * @param int|null $expire 有效时间 0为永久
     * @return boolean
     */
    public static function set($token, $userId, $expire = null)
    {
        return self::init()->set($token, $userId, $expire);
    }

    /**
     * 删除Token(delete别名)
     * @access public
     * @param string $token Token标识
     * @return boolean
     */
    public static function rm($token)
    {
        return self::delete($token);
    }

    /**
     * 删除Token
     * @param string $token 标签名
     * @return bool
     */
    public static function delete($token)
    {
        return self::init()->delete($token);
    }

    /**
     * 清除Token
     * @access public
     * @param int $userId Token标记
     * @return boolean
     */
    public static function clear($userId = null)
    {
        return self::init()->clear($userId);
    }

}
