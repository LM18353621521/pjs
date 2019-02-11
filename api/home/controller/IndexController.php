<?php
namespace api\home\controller;

use cmf\controller\RestBaseController;

/**
 * @title 欢迎页
 * @description 欢迎使用在线接口文档
 */
class IndexController extends RestBaseController
{
    /**
     * @title 首页
     * @description 默认访问接口
     * @author Tiger Yang
     * @url /home/index/index
     * @method GET
     *
     * @return version:版本号
     * @return code:错误码
     */
    public function index()
    {
        $data=[
            'version' => '1.0.0',
            'code'=>[
                '20000' => '默认成功返回码',
                '40000' => '默认错误返回码',
                '40001' => '未登录或登录失效',
                '40002' => '签名验证失败',
                '40003' => '缺少必要参数或参数格式错误',
                '40004' => '登录失败',
            ]
        ];
        $this->success("恭喜您,API访问成功!", $data);
    }




}
