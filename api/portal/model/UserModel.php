<?php
// +----------------------------------------------------------------------
// | bronet [ 以客户为中心 以奋斗者为本 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.bronet.cn All rights reserved.
// +----------------------------------------------------------------------

namespace api\portal\model;
use api\common\model\CommonModel;

class UserModel extends CommonModel
{
    //可查询字段
//    protected $visible = [
//        'articles.id', 'user_nickname', 'avatar', 'signature','user'
//    ];
    //模型关联方法
    protected $relationFilter = ['user'];

    /**
     * 基础查询
     */
    protected function base($query)
    {
        $query->where('cmf_user.user_status', 1);
    }

    /**
     * more 自动转化
     * @param $value
     * @return array
     */
    public function getAvatarAttr($value)
    {
        $value = !empty($value) ? cmf_get_image_url($value) : $value;
        return $value;
    }

    /**
     * 关联 user表
     * @return $this
     */
    public function user()
    {
        return $this->belongsTo('UserModel', 'user_id')->setEagerlyType(1);
    }
}
