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
class UserVisitController extends AdminBaseController
{
    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
        $user_id = input('user_id');
        $user = Db::name('user')->find($user_id);
        $this->user =$user;
        $this->admin_id =session('ADMIN_ID');
        $this->assign('user',$user);
    }

    /**
     * 用户随访列表
     * @return mixed
     */
    public function index()
    {
        $request = input('request.');
        if (!empty($request['user_nickname'])) {
            $where['user_nickname'] = ['like', "%".$request['user_nickname']."%"];
        }
        if (!empty($request['title'])) {
            $where['title'] = $request['title'];
        }
        if (!empty($request['start_time'])&&!empty($request['end_time'])) {
            $start_time = strtotime($request['start_time']);
            $end_time = strtotime($request['end_time'])+86400;
            $where['create_time'] = array('between',[$start_time,$end_time]);
        }
        $where['delete_time'] = 0;

        $keywordComplex = [];
        $usersQuery = Db::name('user_visit');

        $list = $usersQuery->whereOr($keywordComplex)->where($where)->order("create_time DESC")->paginate(15);
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
            $data['admin_id'] = $this->admin_id;
            $data['create_time']=time();
            $data['visit_time']=strtotime($data['visit_time']);
            if($data['id']){
                $res = Db::name('user_visit')->where(array('id'=>$data['id']))->update($data);
                $this->success('编辑成功!', url('UserVisit/index', ['id' => $res]));
            }else{
                $res = Db::name('user_visit')->insert($data);
                $this->success('添加成功!', url('UserVisit/index', ['id' => $res]));
            }
        }

    }

    public function edit()
    {
        $id = $this->request->param('id', 0, 'intval');
        $data = Db::name('user_visit')->find($id);
        $this->assign('data', $data);
        return $this->fetch();
    }

    /**
     * 删除随访记录
     * @throws \think\Exception
     */
    public function delete()
    {
        $param           = $this->request->param();
        $model = Db::name('user_visit');
        if (isset($param['id'])) {
            $id           = $this->request->param('id', 0, 'intval');
            $result       = $model->where(['id' => $id])->find();
            $model->where(['id'=>$id])->delete();
            $this->success("删除成功！", '');
        }
        if (isset($param['ids'])) {
            $ids     = $this->request->param('ids/a');
            $result  = $model->where(['id' => ['in', $ids]])->delete();
            if ($result) {
                $this->success("删除成功！", '');
            }
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
