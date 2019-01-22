<?php
// +----------------------------------------------------------------------
// | bronet [ 以客户为中心 以奋斗者为本 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.bronet.cn All rights reserved.
// +----------------------------------------------------------------------

namespace api\portal\controller;

use cmf\controller\RestBaseController;
use api\portal\model\PortalPostModel;

class PagesController extends RestBaseController
{
    protected $postModel;

    public function __construct(PortalPostModel $postModel)
    {
        parent::__construct();
        $this->postModel = $postModel;
    }

    /**
     * 页面列表
     */
    public function index()
    {
        $params                       = $this->request->get();
        $params['where']['post_type'] = 2;
        $data                         = $this->postModel->getDatas($params);
        $this->success('请求成功!', $data);
    }

    /**
     * 获取页面
     * @param int $id
     */
    public function read($id)
    {
        $params                       = $this->request->get();
        $params['where']['post_type'] = 2;
        $params['id']                 = $id;
        $data                         = $this->postModel->getDatas($params);
        $this->success('请求成功!', $data);
    }
}
