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

use cmf\controller\AdminBaseController;
use think\Db;
error_reporting(E_ERROR | E_PARSE );

/**
 * Class AdminIndexController
 * @package app\user\controller
 *
 * @adminMenuRoot(
 *     'name'   =>'用户管理',
 *     'action' =>'default',
 *     'parent' =>'',
 *     'display'=> true,
 *     'order'  => 10,
 *     'icon'   =>'group',
 *     'remark' =>'用户管理'
 * )
 *
 * @adminMenuRoot(
 *     'name'   =>'用户组',
 *     'action' =>'default1',
 *     'parent' =>'user/AdminIndex/default',
 *     'display'=> true,
 *     'order'  => 10000,
 *     'icon'   =>'',
 *     'remark' =>'用户组'
 * )
 */
class AdminIndexController extends AdminBaseController
{

    /**
     * 后台本站用户列表
     * @adminMenu(
     *     'name'   => '本站用户',
     *     'parent' => 'default1',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '本站用户',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        $where   = ['user_type'=>2];
        $request = input('request.');

        if (!empty($request['user_nickname'])) {
            $where['user_nickname'] = ['like', "%".$request['user_nickname']."%"];
        }
        if (!empty($request['sex'])) {
            $where['sex'] = $request['sex'];
        }
        if (!empty($request['age'])) {
            $where['age'] = $request['age'];
        }
        if (!empty($request['mobile'])) {
            $where['mobile'] = $request['mobile'];
        }
        if (!empty($request['start_time'])&&!empty($request['end_time'])) {
            $start_time = strtotime($request['start_time']);
            $end_time = strtotime($request['end_time'])+86400;
            $where['create_time'] = array('between',[$start_time,$end_time]);
        }

        $role_id = session('ADMIN_ROLE_ID');
        if($role_id==2){
            $admin_id = session('ADMIN_ID');
            $where['bind_admin_id']=$admin_id;
        }


        $keywordComplex = [];
        $usersQuery = Db::name('user');

        $list = $usersQuery->whereOr($keywordComplex)->where($where)->order("create_time DESC")->paginate(10);
        // 获取分页显示
        $list->appends($request);
        $page = $list->render();
        $this->assign('list', $list);
        $this->assign('page', $page);
        // 渲染模板输出
        return $this->fetch();
    }

    /**
     * 添加-本站用户列表
     * @adminMenu(
     *     'name'   => '添加文章',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '添加文章',
     *     'param'  => ''
     * )
     */
    public function add()
    {
        $user_code = Db::name('user')->max('user_code');
        $user_code=$user_code>=10000?($user_code+1):10000;
        $this->assign('user_code',$user_code);

        $poisonList = array(
            'poison0'=>'奋乃静',
            'poison1'=>'盐酸硫利哒嗪',
            'poison2'=>'氯丙嗪',
            'poison3'=>'三氟拉嗪',
            'poison4'=>'氟哌啶醇',
            'poison5'=>'氟哌利多',
            'poison6'=>'利血平',
            'poison7'=>'甲氧氯普胺',
            'poison8'=>'α-甲基多巴',
            'poison9'=>'锂剂',
            'poison10'=>'氟桂利嗪',
            'poison11'=>'桂利嗪级',
        );
        $this->assign('poisonList',$poisonList);

        $other_disease_list = array(
            '1'=>'高血压',
            '2'=>'糖尿病',
            '3'=>'高脂血症',
            '4'=>'脑出血',
            '5'=>'脑梗死',
            '6'=>'血管性痴呆',
            '7'=>'骨关节病',
            '8'=>'性肺病',
            '9'=>'其他',
        );
        $this->assign('other_disease_list',$other_disease_list);

        return $this->fetch();
    }

    /**
     * 添加-本站用户文章提交
     * @adminMenu(
     *     'name'   => '添加文章提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '添加文章提交',
     *     'param'  => ''
     * )
     */
    public function addPost()
    {
        if ($this->request->isPost()) {
            $data   = $this->request->param();
            $user_info = $data['user_info'];
            unset($data['user_info']);
            $data['bind_admin_id'] = session('ADMIN_ID');
            $data['user_type'] = 2;
            $data['birthday'] = strtotime($data['birthday']);
            if(!$data['id']){
                $data['create_time']=time();
                $res = Db::name('user')->insertGetId($data);
                foreach($user_info as&$val){
                    if(is_array($val)){
                        $val = serialize($val);
                    }
                }
                $user_info['user_id']=$res;
                $res = Db::name('user_info')->insert($user_info);
                $msg ="添加成功";
            }else{
                $res = Db::name('user')->where(array('id'=>$data['id']))->update($data);
                foreach($user_info as&$val){
                    if(is_array($val)){
                        $val = serialize($val);
                    }
                }
                $res = Db::name('user_info')->where(array('user_id'=>$data['id']))->update($user_info);
                $msg ="编辑成功";
            }
            $this->success($msg, url('AdminIndex/index', ['id' => $res]));
        }
    }

    public function edit()
    {
        $user_id = $this->request->param('user_id', 0, 'intval');
        $user_model = Db::name('user');
        $user =$user_model->where(array('id'=>$user_id))->find();
        $user_info = Db::name('user_info')->where(array('user_id'=>$user_id))->find();

        $user_info['country'] = unserialize($user_info['country']);
        $user_info['occupation'] = unserialize($user_info['occupation']);
        $user_info['live_status'] = unserialize($user_info['live_status']);
        $user_info['smoking'] = unserialize($user_info['smoking']);
        $user_info['drink_wine'] = unserialize($user_info['drink_wine']);
        $user_info['drink_coffee'] = unserialize($user_info['drink_coffee']);
        $user_info['drink_tea'] = unserialize($user_info['drink_tea']);
        $user_info['poison'] = unserialize($user_info['poison']);
        $user_info['special_poison'] = unserialize($user_info['special_poison']);
        $user_info['vital_sign'] = unserialize($user_info['vital_sign']);
        $user_info['family'] = unserialize($user_info['family']);

        $poisonList = array(
            'poison0'=>'奋乃静',
            'poison1'=>'盐酸硫利哒嗪',
            'poison2'=>'氯丙嗪',
            'poison3'=>'三氟拉嗪',
            'poison4'=>'氟哌啶醇',
            'poison5'=>'氟哌利多',
            'poison6'=>'利血平',
            'poison7'=>'甲氧氯普胺',
            'poison8'=>'α-甲基多巴',
            'poison9'=>'锂剂',
            'poison10'=>'氟桂利嗪',
            'poison11'=>'桂利嗪级',
        );
        $this->assign('poisonList',$poisonList);

        $other_disease_list = array(
            '1'=>'高血压',
            '2'=>'糖尿病',
            '3'=>'高脂血症',
            '4'=>'脑出血',
            '5'=>'脑梗死',
            '6'=>'血管性痴呆',
            '7'=>'骨关节病',
            '8'=>'性肺病',
            '9'=>'其他',
        );
        $this->assign('other_disease_list',$other_disease_list);
        $this->assign('user',$user);
        $this->assign('user_info',$user_info);
        return $this->fetch();
    }




    /**
     * 本站用户拉黑
     * @adminMenu(
     *     'name'   => '本站用户拉黑',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '本站用户拉黑',
     *     'param'  => ''
     * )
     */
    public function ban()
    {
        $id = input('param.id', 0, 'intval');
        if ($id) {
            $result = Db::name("user")->where(["id" => $id, "user_type" => 2])->setField('user_status', 0);
            if ($result) {
                $this->success("会员拉黑成功！", "adminIndex/index");
            } else {
                $this->error('会员拉黑失败,会员不存在,或者是管理员！');
            }
        } else {
            $this->error('数据传入失败！');
        }
    }
    /**
     * 本站用户启用
     * @adminMenu(
     *     'name'   => '本站用户启用',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '本站用户启用',
     *     'param'  => ''
     * )
     */
    public function cancelBan()
    {
        $id = input('param.id', 0, 'intval');
        if ($id) {
            Db::name("user")->where(["id" => $id, "user_type" => 2])->setField('user_status', 1);
            $this->success("会员启用成功！", '');
        } else {
            $this->error('数据传入失败！');
        }
    }
}
