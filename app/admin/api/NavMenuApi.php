<?php
// +----------------------------------------------------------------------
// | bronet [ 以客户为中心 以奋斗者为本 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.bronet.cn All rights reserved.
// +----------------------------------------------------------------------
namespace app\admin\api;

use app\admin\model\NavMenuModel;

class NavMenuApi
{
    // 分类列表 用于模板设计
    public function index($param = [])
    {
        $navMenuModel = new NavMenuModel();

        $where = [];

        if (!empty($param['keyword'])) {
            $where['name'] = ['like', "%{$param['keyword']}%"];
        }
        if (!empty($param['id'])) {
            $where['nav_id'] = $param['id'];
        }

        return $navMenuModel->where($where)->select();
    }

}