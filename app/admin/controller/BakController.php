<?php
namespace app\admin\controller;
use cmf\controller\AdminBaseController;
class BakController extends AdminBaseController {
    public function index(){
        $type             = $this->request->param('tp');
        $name           = $this->request->param('name');
        $sql=new \org\Baksql(config('database'));
        switch ($type)
        {
            case "backup": //备份
                $ret=$sql->backup();
                if($ret['code']=1){
                    $this->success($ret['msg']);
                }else{
                    $this->error($ret['msg']);
                }
                break;
            case "dowonload": //下载
                $sql->downloadFile($name);
                break;
            case "restore": //还原
                $ret= $sql->restore($name);
                if($ret['code']=1){
                    $this->success($ret['msg']);
                }else{
                    $this->error($ret['msg']);
                }
                break;
            case "del": //删除
                $ret= $sql->delfilename($name);
                if($ret['code']=1){
                    $this->success($ret['msg']);
                }else{
                    $this->error($ret['msg']);
                }
                break;
            default: //获取备份文件列表
                return $this->fetch("index",["list"=>$sql->get_filelist()]);
        }
    }

}

?>