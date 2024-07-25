<?php


namespace app\model;


use think\model\concern\SoftDelete;

class AdminUser extends BaseModel
{

    protected $name = 'admin_user';
    protected $order = ['id'=>'desc'];

    public function getPageData($where,$limit){
        return $this->where($where)->where(['is_delete'=>0])->order($this->order)
            ->paginate($limit)->toArray();
    }


}