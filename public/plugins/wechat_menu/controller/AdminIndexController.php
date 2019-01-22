<?php
/**
 * 自定义微信菜单
 * User: Tiger
 * Date: 2017-10-19
 * Time: 9:05
 */
namespace plugins\wechat_menu\controller;

use cmf\controller\PluginBaseController;
use EasyWeChat\Foundation\Application;
use think\Db;

class AdminIndexController extends PluginBaseController
{

    protected $options;
    function _initialize()
    {
        parent::_initialize();
        $this->options= config('wechat_config');
    }

    /**
     * 微信菜单的类型
     * @var array
     */
    protected $menuType = [
        'view'               => '跳转URL',
        'click'              => '点击推事件',
        'scancode_push'      => '扫码推事件',
        'scancode_waitmsg'   => '扫码推事件且弹出“消息接收中”提示框',
        'pic_sysphoto'       => '弹出系统拍照发图',
        'pic_photo_or_album' => '弹出拍照或者相册发图',
        'pic_weixin'         => '弹出微信相册发图器',
        'location_select'    => '弹出地理位置选择器',
    ];
    
    public function index()
    {
        $list=Db::name('wechat_menu')->select()->toArray();
        $list=$this->arr2tree($list, 'index', 'parent_id');
        $this->assign('list',$list);

        return $this->fetch('/admin_index');
    }

    public function sync(){
        $app = new Application($this->options);
        $menu = $app->menu;
        $menus = $menu->current();
        if($menus->is_menu_open==1){
            $selfmenu_info=$menus->selfmenu_info;
            if(isset($selfmenu_info['button'])){
                $data=[];
                $i=1;
                foreach ($selfmenu_info['button'] as $k=>$v){
                    if(isset($v['type'])&&$v['type']=='click' && isset($v['key'])){
                        $type='keys';
                    }elseif (isset($v['type'])&&$v['type']=='click' && isset($v['value'])){
                        $type='text';
                    }elseif(isset($v['type'])){
                        $type=$v['type'];
                    }else{
                        $type='text';
                    }
                    if(!empty($v['view'])){
                        $content=$v['view'];
                    }elseif (!empty($v['key'])){
                        $content=$v['key'];
                    }elseif (!empty($v['value'])){
                        $content=$v['value'];
                    }else{
                        $content='';
                    }
                    $data[]=['index'=>$i,'parent_id'=>0,'type'=>$type,'content'=>$content,'name'=>$v['name']];

                    if(!empty($v['sub_button']['list'])){
                        $j=1;
                        foreach ($v['sub_button']['list'] as $k1=>$v1){
                            if($v1['type']=='click' && isset($v1['key'])){
                                $type1='keys';
                            }elseif ($v1['type']=='click' && isset($v1['value'])){
                                $type1='text';
                            }else{
                                $type1=$v1['type'];
                            }
                            if(!empty($v1['url'])){
                                $content1=$v1['url'];
                            }elseif (!empty($v1['key'])){
                                $content1=$v1['key'];
                            }elseif (!empty($v1['value'])){
                                $content1=$v1['value'];
                            }else{
                                $content1='';
                            }
                            $data[]=['index'=>$i.$j,'parent_id'=>$i,'type'=>$type1,'content'=>$content1,'name'=>$v1['name']];
                            $j++;
                        }
                    }
                    $i++;
                }
                try {
                    Db::transaction(function () use ($data) {
                        Db::name('wechat_menu')->where('1=1')->delete();
                        Db::name('wechat_menu')->insertAll($data);
                    });
                    $this->_push();
                } catch (\Exception $e) {
                    $this->error('微信菜单发布失败，请稍候再试！' . $e->getMessage());
                }
                $this->success('保存发布菜单成功！', '');
            }
        }
        $this->error('微信菜单尚未启用！');
    }
    
    /**
     * 微信菜单编辑
     */
    public function edit()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            !isset($post['data']) && $this->error('访问出错，请稍候再试！');
            // 删除菜单
            if (empty($post['data'])) {
                try {
                    Db::name('wechat_menu')->where('1=1')->delete();
                    $app = new Application($this->options);
                    $menu = $app->menu;
                    $menu->destroy();
                } catch (\Exception $e) {
                    $this->error('删除取消微信菜单失败，请稍候再试！' . $e->getMessage());
                }
                $this->success('删除并取消微信菜单成功！', '');
            }
            // 数据过滤处理
            try {
                foreach ($post['data'] as &$vo) {
                    isset($vo['content']) && ($vo['content'] = str_replace('"', "'", $vo['content']));
                }
                Db::transaction(function () use ($post) {
                    Db::name('wechat_menu')->where('1=1')->delete();
                    Db::name('wechat_menu')->insertAll($post['data']);
                });
                $this->_push();
            } catch (\Exception $e) {
                $this->error('微信菜单发布失败，请稍候再试！' . $e->getMessage());
            }
            $this->success('保存发布菜单成功！', '');
        }
    }

    /**
     * 取消菜单
     */
    public function cancel()
    {
        try {
            $app = new Application($this->options);
            $menu = $app->menu;
            $menu->destroy();
        } catch (\Exception $e) {
            $this->error('菜单取消失败');
        }
        $this->success('菜单取消成功，重新关注可立即生效！', '');
    }

    /**
     * 菜单推送
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function _push()
    {
        list($map, $field) = [['status' => '1'], 'id,index,parent_id,name,type,content'];
        $result =Db::name('wechat_menu')->field($field)->where($map)->order('sort ASC,id ASC')->select()->toArray();
        foreach ($result as &$row) {
            empty($row['content']) && $row['content'] = uniqid();
            if ($row['type'] === 'miniprogram') {
                list($row['appid'], $row['url'], $row['pagepath']) = explode(',', "{$row['content']},,");
            } elseif ($row['type'] === 'view') {
                if (preg_match('#^(\w+:)?//#', $row['content'])) {
                    $row['url'] = $row['content'];
                } else {
                    $row['url'] = url($row['content'], '', true, true);
                }
            } elseif ($row['type'] === 'event') {
                if (isset($this->menuType[$row['content']])) {
                    list($row['type'], $row['key']) = [$row['content'], "wechat_menu#id#{$row['id']}"];
                }
            } elseif ($row['type'] === 'media_id') {
                $row['media_id'] = $row['content'];
            } else {
                $row['key'] = $row['content'];
                !in_array($row['type'], $this->menuType) && $row['type'] = 'click';
            }
            unset($row['content']);
        }
        $menus = $this->arr2tree($result, 'index', 'parent_id', 'sub_button');
        //去除无效的字段
        foreach ($menus as &$item) {
            unset($item['index'], $item['parent_id'], $item['id']);
            if (empty($item['sub_button'])) {
                continue;
            }else{
                unset($item['key']);
            }
            foreach ($item['sub_button'] as &$submenu) {
                unset($submenu['index'], $submenu['parent_id'], $submenu['id']);
            }
            unset($item['type']);
        }
        $app = new Application($this->options);
        $app->menu->add($menus);
    }

    /**
     * 一维数据数组生成数据树
     * @param array $list 数据列表
     * @param string $id 父ID Key
     * @param string $pid ID Key
     * @param string $son 定义子数据Key
     * @return array
     */
    public function arr2tree($list, $id = 'id', $pid = 'pid', $son = 'sub')
    {
        list($tree, $map) = [[], []];
        foreach ($list as $item) {
            $map[$item[$id]] = $item;
        }
        foreach ($list as $item) {
            if (isset($item[$pid]) && isset($map[$item[$pid]])) {
                $map[$item[$pid]][$son][] = &$map[$item[$id]];
            } else {
                $tree[] = &$map[$item[$id]];
            }
        }
        unset($map);
        return $tree;
    }

}