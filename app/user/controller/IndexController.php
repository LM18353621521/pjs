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
namespace app\user\controller;

use app\user\model\UserModel;
use cmf\controller\HomeBaseController;
use think\Db;
use EasyWeChat\Foundation\Application;
use think\Log;

class IndexController extends HomeBaseController
{
    public function callback(){
        $appId=config('wechat_config.app_id');
        $secret=config('wechat_config.secret');
        $config = [
            'app_id'  => $appId,
            'secret'  => $secret,
        ];

        $app = new Application($config);
        $oauth = $app->oauth;
        $user = $oauth->user();
        $wechat_user = $user->toArray();
        if(isset($wechat_user['id'])){
            $openid=$wechat_user['id'];
            $findThirdPartyUser = Db::name("third_party_user")
                ->where('openid', $openid)
                ->where('app_id', $appId)
                ->find();
            if ($findThirdPartyUser) {
                $this->wechatUserLogin($findThirdPartyUser,$openid,$appId);
            }else{
                $this->wechatUserRegister($wechat_user,$openid,$appId);
            }
            $target_url=session('target_url');
            $targetUrl = empty($target_url) ? '/' : $target_url;
            header('location:'. $targetUrl);
        }else{
            Log::write('获取微信用户数据失败');
            $this->error('获取微信用户数据失败');
        }

    }

    /**
     * 微信用户登录
     * @param $findThirdPartyUser
     * @param $openid
     * @param $appId
     */
    protected function wechatUserLogin($findThirdPartyUser,$openid,$appId){
        $currentTime = time();
        $ip          = $this->request->ip(0, true);
        $token = cmf_generate_user_token($findThirdPartyUser['user_id'], 'public');
        $userData = [
            'last_login_ip'   => $ip,
            'last_login_time' => $currentTime,
            'login_times'     => ['exp', 'login_times+1']
        ];
        $row1=Db::name("third_party_user")
            ->where('openid', $openid)
            ->where('app_id', $appId)
            ->update($userData);
        $userInfo=Db::name("third_party_user")
            ->where('openid', $openid)
            ->where('app_id', $appId)->find();
        unset($userData['login_times']);
        $row2=Db::name("user")
            ->where('id', $userInfo['user_id'])
            ->update($userData);
        if($row1!==false&&$row2!==false){
            $userModel=new UserModel();
            $user=$userModel->getUserInfo(['user_id'=>$userInfo['user_id'],'app_id'=>$appId]);
            cmf_update_current_user($user);
            session('token',$token);
            Db::commit();
        }else{
            Db::rollback();
        }
    }

    /**
     * 微信用户注册
     * @param $wechat_user
     * @param $openid
     * @param $appId
     */
    protected function wechatUserRegister($wechat_user,$openid,$appId){
        $currentTime = time();
        $ip          = $this->request->ip(0, true);
        Db::startTrans();
        $userId = Db::name("user")->insertGetId([
            'create_time'     => $currentTime,
            'user_status'     => 1,
            'user_type'       => 2,
            'sex'             => $wechat_user['original']['sex'],
            'user_nickname'   => $wechat_user['nickname'],
            'avatar'          => $wechat_user['avatar'],
            'last_login_ip'   => $ip,
            'last_login_time' => $currentTime,
        ]);

        $row=Db::name("third_party_user")->insert([
            'openid'          => $openid,
            'user_id'         => $userId,
            'third_party'     => 'public',
            'nickname'        => $wechat_user['nickname'],
            'app_id'          => $appId,
            'last_login_ip'   => $ip,
            'union_id'        => '',
            'last_login_time' => $currentTime,
            'create_time'     => $currentTime,
            'login_times'     => 1,
            'status'          => 1,
            'more'            => json_encode($wechat_user)
        ]);
        if($userId && $row){
            $token = cmf_generate_user_token($userId, 'public');
            $userModel=new UserModel();
            $user=$userModel->getUserInfo(['user_id'=>$userId,'app_id'=>$appId]);
            cmf_update_current_user($user);
            session('token',$token);
            Db::commit();
        }else{
            Db::rollback();
        }
    }

    /**
     * 前台用户首页(公开)
     */
    public function index()
    {
        $id   = $this->request->param("id", 0, "intval");
        $userQuery = Db::name("User");
        $user = $userQuery->where('id',$id)->find();
        if (empty($user)) {
            session('user',null);
            $this->error("查无此人！");
        }
        $this->assign($user);
        return $this->fetch(":index");

    }

    /**
     * 前台ajax 判断用户登录状态接口
     */
    function isLogin()
    {
        if (cmf_is_user_login()) {
            $this->success("用户已登录",null,['user'=>cmf_get_current_user()]);
        } else {
            $this->error("此用户未登录!");
        }
    }

    /**
     * 退出登录
    */
    public function logout()
    {
        session("user", null);//只有前台用户退出
        return redirect($this->request->root() . "/");
    }

}
