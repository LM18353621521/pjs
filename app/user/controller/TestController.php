<?php
// +----------------------------------------------------------------------
// | bronet [ 以客户为中心 以奋斗者为本 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.bronet.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Powerless < wzxaini9@gmail.com>
// +----------------------------------------------------------------------
namespace app\user\controller;


use cmf\controller\WeChatBaseController;
use EasyWeChat\Foundation\Application;

class TestController extends WeChatBaseController
{

    function _initialize()
    {
        parent::_initialize();
        $this->checkWeChatUserLogin();
    }

    public function index(){
        $options=config('wechat_config');
        $app = new Application($options);

        $js = $app->js;
        $jssdk=$js->config(array('onMenuShareQQ', 'onMenuShareWeibo'), false,false,true);
        $js->setUrl('http://www.baidu.com');
        $this->assign('jssdk',$jssdk);
    }


}