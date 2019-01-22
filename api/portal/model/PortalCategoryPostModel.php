<?php
// +----------------------------------------------------------------------
// | bronet [ 以客户为中心 以奋斗者为本 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.bronet.cn All rights reserved.
// +----------------------------------------------------------------------
namespace api\portal\model;

use think\Model;

class PortalCategoryPostModel extends Model
{
    /**
     * 基础查询
     */
    protected function base($query)
    {
        $query->where('status', 1);
    }
}