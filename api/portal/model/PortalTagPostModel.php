<?php
// +----------------------------------------------------------------------
// | bronet [ 以客户为中心 以奋斗者为本 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.bronet.cn All rights reserved.
// +----------------------------------------------------------------------

namespace api\portal\model;

use think\Model;

class PortalTagPostModel extends Model
{
    /**
     * 获取指定id相关的文章id数组
     * @param $post_id  文章id
     * @return array    相关的文章id
     */
    function getRelationPostIds($post_id)
    {
        $tagIds  = $this->where('post_id', $post_id)
            ->column('tag_id');
        $postIds = $this->whereIn('tag_id', $tagIds)
            ->column('post_id');
        return array_unique($postIds);
    }
}