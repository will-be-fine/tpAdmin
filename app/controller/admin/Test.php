<?php
namespace app\controller\admin;

use app\controller\BaseController;

class Test extends BaseController
{
    public function index()
    {

        return "hello admin";
    }

    public function hello($name = 'ThinkPHP6')
    {
        return 'hello,' . $name;
    }
}
