<?php
/**
 * 后台操作日志模型
 */
namespace app\model\admin\action;

use app\model\BaseModel;

class Log extends BaseModel
{

    //模型名
    protected $name = 'admin_action_log';

    //附加属性
    protected $append = [];

    //时间戳字段转换
    

    //表名
    

    //关联模型
    public function adminUser(){
        return $this->belongsTo('app\model\admin\User','admin_id','id');
    }

    //新增属性的方法
    public function getCreateTimeIntAttr($value, $data)
	{
		return isset($data['create_time']) ? strtotime($data['create_time']) : 0;
	}
}
