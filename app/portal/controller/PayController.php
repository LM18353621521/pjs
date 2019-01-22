<?php

namespace app\portal\controller;

use cmf\controller\HomeBaseController;
use EasyWeChat\Foundation\Application;
use EasyWeChat\Payment\Order;

/**
 * 微信支付,退款，提现DEMO
 * Class PayController
 * @package app\portal\controller
 */
class PayController extends HomeBaseController
{
    protected $options;
    function _initialize()
    {
        parent::_initialize();
        $this->options = [
            'app_id'  => config('wechat_config.app_id'),
            'secret'  => config('wechat_config.secret'),
            'payment' => config('wechat_config.payment'),
        ];
    }

    /**
     * 微信支付
     */
    public function index(){
        $attributes = [
            'trade_type'       => 'JSAPI',
            'body'             => '百荣科技',
            'detail'           => '以客户为中心 以奋斗者文本',
            'out_trade_no'     => cmf_get_order_sn(),
            'total_fee'        => 1, // 单位：分
            'notify_url'       => url('portal/pay/notify','','',true), // 支付结果通知网址，如果不设置则会使用配置里的默认地址
            'openid'           => cmf_get_current_user_openid(), // trade_type=JSAPI，此参数必传，用户在商户appid下的唯一标识，
        ];
        $order = new Order($attributes);

        $app = new Application($this->options);
        $payment = $app->payment;
        $result = $payment->prepare($order);

        if ($result->return_code == 'SUCCESS' && $result->result_code == 'SUCCESS'){
            $prepayId = $result->prepay_id;
            $jsApiParameters=$payment->configForJSSDKPayment($prepayId);
            $this->assign('jsApiParameters',json_encode($jsApiParameters));
            return $this->fetch();
        }else{
            $this->error('支付参数错误','',$result);
        }

    }

    /**
     * 支付回调
     * @throws \EasyWeChat\Core\Exceptions\FaultException
     */
    public function notify(){
        cache('nnn',111);
        $app = new Application($this->options);
        $response = $app->payment->handleNotify(function($notify, $successful){
            cache('notify',$notify);
            cache('successful',$successful);
            /*这里是支付回调逻辑处理，一下是DEMO*/

//            // 使用通知里的 "微信支付订单号" 或者 "商户订单号" 去自己的数据库找到订单
//            $out_trade_no=$notify->out_trade_no;
//            $order = Db::name('order')->where('order_sn',$out_trade_no)->find();
//            if (!$order) { // 如果订单不存在
//                return 'Order not exist.'; // 告诉微信，我已经处理完了，订单没找到，别再通知我了
//            }
//            // 如果订单存在
//            // 检查订单是否已经更新过支付状态
//            if ($order['pay_time']>0) { // 假设订单字段“支付时间”不为空代表已经支付
//                return true; // 已经支付成功了就不再更新了
//            }
//
//            // 用户是否支付成功
//            if ($successful) {
//                // 回填微信的订单号
//                $update['transaction_id']=$notify->transaction_id;
//                $update['pay_time']=time();
//                // 不是已经支付状态则修改为已经支付状态
//                $update['status'] = 2;
//            } else { // 用户支付失败
//                $update['status']=9;
//            }
//            Db::name('order')->where('order_sn',$out_trade_no)->update($update);

            return true; // 返回处理完成
        });

        $response->send();
    }


    /**
     * 查询订单
     */
    public function checkOrder(){
        $app = new Application($this->options);
        $payment = $app->payment;
        $orderNo = "2018080397545210";//商户系统内部的订单号（out_trade_no）
        $result=$payment->query($orderNo);
        var_dump($result);
    }

    /**
     * 退款
     */
    public function refund(){
        //todo 退款逻辑应该加入百荣签名验证规则，避免出现被盗用
        /*$param=$this->request->param();
        $signature = $param['s'];
        $arithmetic['timeStamp']= $param['t'];
        $arithmetic['randomStr']= $param['r'];
        $arithmetic['orderSn']= $param['o'];
        $str = arithmetic($arithmetic);
        if($str != $signature){
        $this->error('签名验证失败');
        }*/
        $app = new Application($this->options);
        $payment = $app->payment;
        //使用商户订单号退款  PS.其他形式参考文档
        $orderNo = "2018080397100101";//商户系统内部的订单号（out_trade_no）
        $refundNo = cmf_get_order_sn();//退款单号
        $result = $payment->refund($orderNo, $refundNo, 1, 1); // 总金额 100， 退款 80，refundFee可选（为空时全额退款）
        var_dump($result);
    }

    /**
     * 查询退款
     */
    public function checkRefund(){
        $app = new Application($this->options);
        $payment = $app->payment;
        $outTradeNo="2018080210097519";//商户系统内部的订单号（out_trade_no）
        $result = $payment->queryRefund($outTradeNo);
        var_dump($result);
    }

    /**
     * 退款结果回调
     */
    public function refundNotify() {
        cache('test',123123);
        $app = new Application($this->options);
        $response = $app->payment->handleRefundNotify(function ($message, $reqInfo) {
            cache('message',$message);
            cache('reqInfo',$reqInfo);
            // 其中 $message['req_info'] 获取到的是加密信息
            // $reqInfo 为 message['req_info'] 解密后的信息
            // 你的业务逻辑...
            return true; // 返回 true 告诉微信“我已处理完成”
            // 或返回错误原因 $fail('参数格式校验错误');
        });

        $response->send();
    }

    /**
     * 红包
     */
    public function luckyMoney(){
        //todo 退款逻辑应该加入百荣签名验证规则，避免出现被盗用
       /* $param=$this->request->param();
        $signature = $param['s'];
        $arithmetic['timeStamp']= $param['t'];
        $arithmetic['randomStr']= $param['r'];
        $arithmetic['orderSn']= $param['o'];
        $str = arithmetic($arithmetic);
        if($str != $signature){
            $this->error('签名验证失败');
        }*/

        $app = new Application($this->options);
        $luckyMoney = $app->lucky_money;
        $luckyMoneyData = [
            'mch_billno'       => 'xy123456',
            'send_name'        => '测试红包',
            're_openid'        => 'oxTWIuGaIt6gTKsQRLau2M0yL16E',
            'total_num'        => 1,  //普通红包固定为1，裂变红包不小于3
            'total_amount'     => 100,  //单位为分，普通红包不小于100，裂变红包不小于300
            'wishing'          => '祝福语',
            'client_ip'        => '192.168.0.1',  //可不传，不传则由 SDK 取当前客户端 IP
            'act_name'         => '测试活动',
            'remark'           => '测试备注',
            // ...
        ];
        //普通红包
        $result = $luckyMoney->send($luckyMoneyData, \EasyWeChat\Payment\LuckyMoney\API::TYPE_NORMAL);
        var_dump($result);
    }

    /**
     * 查询红包
     */
    public function checkLuckyMoney(){
        $mchBillNo = "商户系统内部的订单号（mch_billno）";
        $app = new Application($this->options);
        $luckyMoney = $app->lucky_money;
        $luckyMoney->query($mchBillNo);
    }

    /**
     * 生成签名DEMO
     * @return string
     */
    public function getSignatureDemo(){
        $timeStamp = time();
        $randomStr = cmf_random_string(8);
        $arithmetic['timeStamp']= $timeStamp;
        $arithmetic['randomStr']= $randomStr;
        $signature = arithmetic($arithmetic);
        $notifyParam=array('t'=>$timeStamp,'r'=>$randomStr,'s'=>$signature);
        $url=url('your url 表达式',$notifyParam,true,true);
        return $url;
    }


}