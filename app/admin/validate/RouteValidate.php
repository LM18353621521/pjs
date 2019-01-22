<?php
// +----------------------------------------------------------------------
// | bronet [ 以客户为中心 以奋斗者为本 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.bronet.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\validate;

use think\Validate;

class RouteValidate extends Validate
{
    protected $rule = [
        'url'      => 'require',
        'full_url' => 'require',
    ];

    protected $message = [
        'url.require'      => '显示网址不能为空',
        'full_url.require' => '原始网址不能为空',
    ];

}