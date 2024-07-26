<?php
namespace app\controller;

use app\controller\BaseController;

class Index extends BaseController
{
    public function index()
    {
        return redirect("/v1/admin/index.html");
    }

    public function hello($name = 'ThinkPHP6')
    {
        return 'hello,' . $name;
    }
}
