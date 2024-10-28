<?php

namespace library;

/**
 * 常用函数
 * Class CommonFun
 * @package laytp\library
 */
class CommonFun
{
    /**
     * 统一处理post数据
     * @param $post
     * @return mixed
     */
    public static function filterPostData($post)
    {
        if (!$post) {
            return [];
        }
        //处理数组
        foreach ($post as $k => $v) {
            if (is_array($v)) {
                $post[$k] = implode(',', $v);
            }
        }
        return $post;
    }

    /**
     * 上传图片id转图片地址
     * @param $fileId
     */
    public static function uploadId2Path($fileId){



    }
}