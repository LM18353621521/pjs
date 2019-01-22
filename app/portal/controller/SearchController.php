<?php
// +----------------------------------------------------------------------
// | bronet [ 以客户为中心 以奋斗者为本 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.bronet.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <bronet@126.com>
// +----------------------------------------------------------------------
namespace app\portal\controller;

use cmf\controller\HomeBaseController;

class SearchController extends HomeBaseController
{
    public function index()
    {
        $keyword = $this->request->param('keyword');

        if (empty($keyword)) {
            $this -> error("关键词不能为空！请重新输入！");
        }

        $this -> assign("keyword", $keyword);
        return $this->fetch('/search');
    }
}
