<?php
// +----------------------------------------------------------------------
// | bronet [ 以客户为中心 以奋斗者为本 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.bronet.cn All rights reserved.
// +----------------------------------------------------------------------
namespace api\user\controller;

use cmf\controller\RestUserBaseController;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use think\Db;

/**
 * @title 文件上传
 * @description 文件上传
 * @package api\wxapp\controller
 */
class UploadController extends RestUserBaseController
{

    /**
     * @title 上传单个文件
     * @description 上传单个文件
     * @author Tiger Yang
     * @url /user/upload/one
     * @method POST
     *
     * @header name:XX-Token require:1 default: desc:登录标识
     * @header name:XX-Device-Type require:0 default:wxapp desc:设备类型
     *
     * @param name:file type:file require:1 other: desc:上传文件
     */
    public function one()
    {
        $file = $this->request->file('file');
        // 移动到框架应用根目录/public/upload/ 目录下
        $info     = $file->validate([
            /*'size' => 15678,*/
            'ext' => 'jpg,png,gif'
        ]);
        $fileMd5  = $info->md5();
        $fileSha1 = $info->sha1();

        $findFile = Db::name("asset")->where('file_md5', $fileMd5)->where('file_sha1', $fileSha1)->find();

        if (!empty($findFile)) {
            $this->success("上传成功!", ['url' => cmf_get_asset_url($findFile['file_path']), 'filename' => $findFile['filename']]);
        }
        $info = $info->move(ROOT_PATH . 'public' . DS . 'upload');
        if ($info) {
            $saveName     = $info->getSaveName();
            $originalName = $info->getInfo('name');//name,type,size
            $fileSize     = $info->getInfo('size');
            $suffix       = $info->getExtension();

            $fileKey = $fileMd5 . md5($fileSha1);

            $userId = $this->getUserId();
            Db::name('asset')->insert([
                'user_id'     => $userId,
                'file_key'    => $fileKey,
                'filename'    => $originalName,
                'file_size'   => $fileSize,
                'file_path'   => cmf_get_asset_url($saveName),
                'file_md5'    => $fileMd5,
                'file_sha1'   => $fileSha1,
                'create_time' => time(),
                'suffix'      => $suffix
            ]);

            $storage = cmf_get_option('storage');

            if (isset($storage['type'])&&$storage['type']=='Qiniu') {
                $this->uploadToQiniu($saveName);
            }

            $this->success("上传成功!", ['url' => cmf_get_asset_url($saveName), 'filename' => $originalName]);
        } else {
            // 上传失败获取错误信息
            $this->error($file->getError());
        }

    }


    /**
     * 上传到七牛云
     * @param $save_name
     * @param bool $is_del_local
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function uploadToQiniu($save_name,$is_del_local=true){
        $plugin=Db::name('plugin')->field('config')->where(['name'=>'Qiniu'])->find();
        $config=json_decode($plugin['config'],true);

        $filePath = './../upload/'.$save_name;
        // 上传到七牛后保存的文件名
        $key =$save_name;
        require_once VENDOR_PATH . 'qiniu/php-sdk/autoload.php';
        // 需要填写你的 Access Key 和 Secret Key
        $accessKey = $config['accessKey'];
        $secretKey = $config['secretKey'];
        // 构建鉴权对象
        $auth = new Auth($accessKey,$secretKey);
        // 要上传的空间
        $bucket = $config['bucket'];

        $token = $auth->uploadToken($bucket);
        // 初始化 UploadManager 对象并进行文件的上传
        $uploadMgr = new UploadManager();
        // 调用 UploadManager 的 putFile 方法进行文件的上传
        list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
        if($is_del_local){
            unlink($filePath);
        }
        if ($err !== null) {
            return ["err"=>1,"msg"=>$err,"data"=>""];
        } else {
            return ["err"=>0,"msg"=>"上传完成","data"=>cmf_get_image_url($ret['key'])];
        }
    }


}
