<?php
// +----------------------------------------------------------------------
// | bronet [ 以客户为中心 以奋斗者为本 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.bronet.cn All rights reserved.
// +----------------------------------------------------------------------
namespace api\wxapp\controller;

use think\Db;
use cmf\controller\RestBaseController;
use wxapp\aes\WXBizDataCrypt;
use think\Validate;

/**
 * @title 公共模块
 * @description 公共模块
 * @package api\wxapp\controller
 */
class PublicController extends RestBaseController
{
    /**
     * @title 获取sessionKey和openid
     * @description 小程序登录注册
     * @author Tiger Yang
     * @url /wxapp/public/getSessionKey
     * @method POST
     *
     * @param name:code type:string require:1 other: desc:code
     *
     * @return session_key:session_key
     * @return openid:openid
     */
    public function getSessionKey(){
        $validate = new Validate([
            'code'           => 'require',
        ]);

        $validate->message([
            'code.require'           => '缺少参数code!',
        ]);

        $data = $this->request->param();
        if (!$validate->check($data)) {
            $this->error(['code'=>'40003','msg'=>$validate->getError()]);
        }

        $code      = $data['code'];
        $appId     = config('app_id');
        $appSecret = config('app_secret');

        $response = cmf_curl_get("https://api.weixin.qq.com/sns/jscode2session?appid=$appId&secret=$appSecret&js_code=$code&grant_type=authorization_code");

        $response = json_decode($response, true);
        if (!empty($response['errcode'])) {
            $this->error(['code'=>'41001','msg'=>'操作失败:'.$response['errcode']]);
        }
        $this->success('获取成功',$response);
    }

    /**
     * @title 小程序登录注册
     * @description 小程序登录注册
     * @author Tiger Yang
     * @url /wxapp/public/login
     * @method POST
     *
     * @param name:openid type:string require:1 other: desc:openid
     * @param name:session_key type:string require:1 other: desc:session_key
     * @param name:encrypted_data type:string require:1 other: desc:encrypted_data
     * @param name:iv type:string require:1 other: desc:iv
     *
     * @return token:登录唯一标识
     */
    public function login()
    {
        $validate = new Validate([
            'openid'           => 'require',
            'session_key'           => 'require',
            'encrypted_data' => 'require',
            'iv'             => 'require',
        ]);

        $validate->message([
            'openid.require'           => '缺少参数openid!',
            'session_key.require'           => '缺少参数session_key!',
            'encrypted_data.require' => '缺少参数encrypted_data!',
            'iv.require'             => '缺少参数iv!',
        ]);

        $data = $this->request->param();
        if (!$validate->check($data)) {
            $this->error(['code'=>'40003','msg'=>$validate->getError()]);
        }

        $appId     = config('app_id');

        $openid     = $data['openid'];
        $sessionKey = $data['session_key'];

        $pc      = new WXBizDataCrypt($appId, $sessionKey);
        $errCode = $pc->decryptData($data['encrypted_data'], $data['iv'], $wxUserData);

        if ($errCode != 0) {
            $this->error(['code'=>'41002','msg'=>'检验数据失败!'],['errCode'=>$errCode,'param'=>$data]);
        }

        $findThirdPartyUser = Db::name("third_party_user")
            ->where('openid', $openid)
            ->where('app_id', $appId)
            ->find();

        $currentTime = time();
        $ip          = $this->request->ip(0, true);

        $wxUserData['sessionKey'] = $sessionKey;
        unset($wxUserData['watermark']);

        if ($findThirdPartyUser) {
            $token = cmf_generate_user_token($findThirdPartyUser['user_id'], 'wxapp');

            $userData = [
                'last_login_ip'   => $ip,
                'last_login_time' => $currentTime,
                'login_times'     => ['exp', 'login_times+1'],
                'more'            => json_encode($wxUserData)
            ];

            if (isset($wxUserData['unionId'])) {
                $userData['union_id'] = $wxUserData['unionId'];
            }

            Db::name("third_party_user")
                ->where('openid', $openid)
                ->where('app_id', $appId)
                ->update($userData);
            $this->success("登录成功!", ['token' => $token]);
        } else {

            Db::startTrans();
            $userId = Db::name("user")->insertGetId([
                'create_time'     => $currentTime,
                'user_status'     => 1,
                'user_type'       => 2,
                'sex'             => $wxUserData['gender'],
                'user_nickname'   => $wxUserData['nickName'],
                'avatar'          => $wxUserData['avatarUrl'],
                'last_login_ip'   => $ip,
                'last_login_time' => $currentTime
            ]);

            $row=Db::name("third_party_user")->insert([
                'openid'          => $openid,
                'user_id'         => $userId,
                'third_party'     => 'wxapp',
                'app_id'          => $appId,
                'last_login_ip'   => $ip,
                'union_id'        => isset($wxUserData['unionId']) ? $wxUserData['unionId'] : '',
                'last_login_time' => $currentTime,
                'create_time'     => $currentTime,
                'login_times'     => 1,
                'status'          => 1,
                'more'            => json_encode($wxUserData)
            ]);

            if($userId && $row){
                Db::commit();
                $token = cmf_generate_user_token($userId, 'wxapp');
                $this->success("登录成功!", ['token' => $token]);
            }else{
                Db::rollback();
                $this->error(['code'=>'40004','msg'=>'登录失败']);
            }

        }

    }

}
