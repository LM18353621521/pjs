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
 * 患者帕金森病情管理
 * Class PdDiseaseController
 * @package app\user\controller
 */
class PdDiseaseController extends AdminBaseController
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
     * 数据列表
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

        $keywordComplex = [];
        $usersQuery = Db::name('user_pd_disease');

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
     * 添加数据
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
     * 添加/编辑数据提交
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
        $model = Db::name('user_pd_disease');
        if ($this->request->isPost()) {
            $data   = $this->request->param();
            if(empty($data['id'])){
                $data['admin_id'] = $this->admin_id;
                $data['create_time']=time();
                $res = $model->insert($data);
                adminLog("添加帕金森病情(ID:$res)");
                $this->success('添加成功!', url('PdDisease/index', ['user_id' => $data['user_id']]));
            }else{
                $data['update_time']=time();
                $res = $model->where(array('id'=>$data['id']))->update($data);
                adminLog("编辑帕金森病情(ID:".$data['id'].")");
                $this->success('编辑成功!', url('PdDisease/index', ['id' => $res]));
            }
        }

    }

    /**
     * @return mixed编辑数据
     */
    public function edit()
    {
        $model = Db::name('user_pd_disease');
        $id = $this->request->param('id', 0, 'intval');
        $data = $model->find($id);
        $this->assign('data', $data);
        return $this->fetch();
    }

    /**
     * 删除数据
     * @throws \think\Exception
     */
    public function delete()
    {
        $model = Db::name('user_pd_disease');
        $param           = $this->request->param();
        if (isset($param['id'])) {
            $id           = $this->request->param('id', 0, 'intval');
            $result       = $model->where(['id' => $id])->find();
            $model->where(['id'=>$id])->delete();
            adminLog("删除帕金森病情(ID:".$id.")");
            $this->success("删除成功！", '');
        }
        if (isset($param['ids'])) {
            $ids     = $this->request->param('ids/a');
            $result  = $model->where(['id' => ['in', $ids]])->delete();
            adminLog("删除帕金森病情(ID:".implode(",",$ids).")");
            if ($result) {
                $this->success("删除成功！", '');
            }
        }
    }
}
