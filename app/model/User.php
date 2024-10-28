<?php


namespace app\model;


class User extends BaseModel
{

    protected $name = 'admin_user';
    protected $order = ['id'=>'desc'];

    public function getPageData($where,$limit){
        return $this->where($where)->where(['is_deleted'=>0])->order($this->order)
            ->paginate($limit)->toArray();
    }


}