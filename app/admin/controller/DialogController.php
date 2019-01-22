<?php
// +----------------------------------------------------------------------
// | bronet [ 以客户为中心 以奋斗者为本 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.bronet.cn All rights reserved.
// +----------------------------------------------------------------------
namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use think\Db;

class DialogController extends AdminBaseController
{
    public function _initialize()
    {

    }

    public function map()
    {
        $location = $this->request->param('location');
        $location = explode(',', $location);
        $lng      = empty($location[0]) ? 116.424966 : $location[0];
        $lat      = empty($location[1]) ? 39.907851 : $location[1];

        $this->assign(['lng' => $lng, 'lat' => $lat]);
        return $this->fetch();
    }

    public function map2()
    {
        $location = $this->request->param('location');
        $location = explode(',', $location);
        $lng      = empty($location[0]) ? 116.424966 : $location[0];
        $lat      = empty($location[1]) ? 39.907851 : $location[1];

        $this->assign(['lng' => $lng, 'lat' => $lat]);
        return $this->fetch();
    }

    public function getShopHtml(){
        $data = $this->request->param();
        $where=[];
        if (!empty($data['category_id'])) {
            $where['category_id']=$data['category_id'];
        }
        $list=Db::name('shop')->where($where)->select()->toArray();
        if(!empty($list)){
            $this->success('','',$list);
        }else{
            $this->error('该分类下没有店铺');
        }

    }

}