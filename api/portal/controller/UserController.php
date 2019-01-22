<?php
// +----------------------------------------------------------------------
// | bronet [ 以客户为中心 以奋斗者为本 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.bronet.cn All rights reserved.
// +----------------------------------------------------------------------
namespace api\portal\controller;

use api\portal\model\PortalPostModel;
use cmf\controller\RestBaseController;

class UserController extends RestBaseController
{
    protected $postModel;

    public function __construct(PortalPostModel $postModel)
    {
        parent::__construct();
        $this->postModel = $postModel;
    }

    /**
     * 会员文章列表
     */
    public function articles()
    {
        $userId   = $this->request->param('user_id', 0, 'intval');

        if(empty($userId)){
            $this->error('用户id不能空！');
        }

        $data     = $this->request->param();
        $articles = $this->postModel->setCondition($data)->where(['user_id' => $userId])->select();

        if (count($articles) == 0) {
            $this->error('没有数据');
        } else {
            $this->success('ok', ['list' => $articles]);
        }

    }

}
