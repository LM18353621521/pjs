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
use app\portal\model\PortalTagModel;

class TagController extends HomeBaseController
{
    public function index()
    {
        $id             = $this->request->param('id', 0, 'intval');
        $portalTagModel = new PortalTagModel();

        $tag = $portalTagModel->where('id', $id)->where('status', 1)->find();

        $this->assign('tag', $tag);

        return $this->fetch('/tag');
    }

}
