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
namespace app\portal\controller;


use EasyWeChat\Foundation\Application;

/**
 * 微信服务器接口
 * Class WechatController
 * @package app\portal\controller
 */
class WechatController
{

    public function index(){
        $app=new Application(config('wechat_config'));
        $server = $app->server;

        $server->setMessageHandler(function ($message) {
            switch ($message->MsgType) {
                case 'event':
                    switch ($message->Event) {
                        case 'subscribe':
                            return '欢迎关注';
                            break;
                        case 'CLICK':
                            $content = $message->EventKey; // 获取key
                            return '收到CLICK事件：'.$content;
                            break;
                        default:
                            return '收到event消息';
                            break;
                    }
                    break;
                case 'text':
                    return '收到文字消息';
                    break;
                default:
                    return '收到其它消息';
                    break;
            }
        });

        $response = $server->serve();

        $response->send();
    }

}