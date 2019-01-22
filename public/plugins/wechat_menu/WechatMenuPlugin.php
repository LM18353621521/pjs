<?php
/**
 * 微信自定义菜单
 * User: Tiger
 * Date: 2018-08-07
 * Time: 8:20
 */
namespace plugins\wechat_menu;

use cmf\lib\Plugin;

class WechatMenuPlugin extends Plugin
{
    public $info = [
        'name'        => 'WechatMenu',
        'title'       => '微信自定义菜单',
        'description' => '微信自定义菜单',
        'status'      => 1,
        'author'      => 'Tiger',
        'version'     => '1.0'
    ];

    public $hasAdmin = 1;//插件是否有后台管理界面

    // 插件安装
    public function install()
    {
        return true;
    }

    // 插件卸载
    public function uninstall()
    {
        return true;
    }
}