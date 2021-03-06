<?php
// +----------------------------------------------------------------------
// | bronet [ 以客户为中心 以奋斗者为本 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.bronet.cn All rights reserved.
// +----------------------------------------------------------------------

namespace api\user\model;

use api\common\model\CommonModel;

class UserFavoriteModel extends CommonModel
{
    /**
     * [base 基础查询条件]
     */
    protected function base($query)
    {
        $query->field('id,title,url,description,create_time');
    }

    /**
     * 关联表
     * @param  [string] $table_name [关联表名]
     */
    protected function unionTable($table_name)
    {
        return $this->hasOne($table_name . 'Model', 'object_id');
    }

    /**
     * url   自动转化
     * @param $value
     * @return string
     */
    public function getUrlAttr($value)
    {
        $url = json_decode($value,true);
        if (!empty($url)) {
            $url = url($url['action'], $url['param'], true, true);
        } else {
            $url = '';
        }
        return $url;
    }

    /**
     * 获取收藏内容
     * @param  [array] $data [select,find查询结果]
     * @return [array]       [收藏对应表的内容]
     */
    public function getFavorite($data)
    {
        if (!is_string($data[0])) {
            foreach ($data as $key => $value) {
                $where[$value['table_name']][] = $value['object_id'];
            }
            foreach ($where as $key => $value) {
                $favoriteData[] = $this->unionTable($key)->select($value);
            }
        } else {
            $favoriteData = $this->unionTable($data['table_name'])->find($data['object_id']);
        }

        return $favoriteData;
    }

    /**
     * [setFavorite 设置收藏]
     * @Author:   wuwu<15093565100@163.com>
     * @DateTime: 2017-08-03T09:16:37+0800
     * @since:    1.0
     */
    public function setFavorite($data)
    {
        //获取收藏内容信息
        $Favorite = self::create($data);
        return $Favorite->id;
    }

    /**
     * [unsetFavorite 取消收藏]
     * @Author:   wuwu<15093565100@163.com>
     * @DateTime: 2017-08-03T09:17:30+0800
     * @since:    1.0
     * @return    [type]                    [description]
     */
    public function unsetFavorite($id)
    {
        return self::destroy($id); //执行删除
    }
}
