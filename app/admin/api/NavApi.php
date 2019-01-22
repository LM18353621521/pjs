<?php
// +----------------------------------------------------------------------
// | bronet [ 以客户为中心 以奋斗者为本 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.bronet.cn All rights reserved.
// +----------------------------------------------------------------------
namespace app\admin\api;

use app\admin\model\NavModel;

class NavApi
{
    // 分类列表 用于模板设计
    public function index($param = [])
    {
        $navModel = new NavModel();

        $where = [];

        if (!empty($param['keyword'])) {
            $where['name'] = ['like', "%{$param['keyword']}%"];
        }

        return $navModel->where($where)->select();
    }

}