<?php
// +----------------------------------------------------------------------
// | bronet [ 以客户为中心 以奋斗者为本 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.bronet.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: kane <chengjin005@163.com>
// +----------------------------------------------------------------------
namespace app\user\controller;

use cmf\controller\AdminBaseController;
use cmf\lib\Upload;
use Qiniu\Auth;
use think\Db;

/**
 * 附件上传控制器
 * Class Asset
 * @package app\asset\controller
 */
class AssetController extends AdminBaseController
{
    public function _initialize()
    {
        $adminId = cmf_get_current_admin_id();
        $userId  = cmf_get_current_user_id();
        if (empty($adminId) && empty($userId)) {
            exit("非法上传！");
        }
    }

    /**
     * webuploader 上传
     */
    public function webuploader()
    {
        if ($this->request->isPost()) {

            $uploader = new Upload();

            $result = $uploader->upload();

            if ($result === false) {
                $this->error($uploader->getError());
            } else {
                $this->success("上传成功!", '', $result);
            }

        } else {
            $uploadSetting = cmf_get_upload_setting();

            $arrFileTypes = [
                'image' => ['title' => 'Image files', 'extensions' => $uploadSetting['file_types']['image']['extensions']],
                'video' => ['title' => 'Video files', 'extensions' => $uploadSetting['file_types']['video']['extensions']],
                'audio' => ['title' => 'Audio files', 'extensions' => $uploadSetting['file_types']['audio']['extensions']],
                'file'  => ['title' => 'Custom files', 'extensions' => $uploadSetting['file_types']['file']['extensions']]
            ];

            $arrData = $this->request->param();
            if (empty($arrData["filetype"])) {
                $arrData["filetype"] = "image";
            }

            $fileType = $arrData["filetype"];

            if (array_key_exists($arrData["filetype"], $arrFileTypes)) {
                $extensions                = $uploadSetting['file_types'][$arrData["filetype"]]['extensions'];
                $fileTypeUploadMaxFileSize = $uploadSetting['file_types'][$fileType]['upload_max_filesize'];
            } else {
                $this->error('上传文件类型配置错误！');
            }

            $this->assign('filetype', $arrData["filetype"]);
            $this->assign('extensions', $extensions);
            $this->assign('upload_max_filesize', $fileTypeUploadMaxFileSize * 1024);
            $this->assign('upload_max_filesize_mb', intval($fileTypeUploadMaxFileSize / 1024));
            $maxFiles  = intval($uploadSetting['max_files']);
            $maxFiles  = empty($maxFiles) ? 20 : $maxFiles;
            $chunkSize = intval($uploadSetting['chunk_size']);
            $chunkSize = empty($chunkSize) ? 512 : $chunkSize;
            $this->assign('max_files', $arrData["multi"] ? $maxFiles : 1);
            $this->assign('chunk_size', $chunkSize); //// 单位KB
            $this->assign('multi', $arrData["multi"]);
            $this->assign('app', $arrData["app"]);

            $storage = cmf_get_option('storage');
            if ($storage['type'] == 'Qiniu'){
                $plugin=Db::name('plugin')->field('config')->where(['name'=>'Qiniu'])->find();
                $config=json_decode($plugin['config'],true);
                $this->assign('qiniu_config', $config);

                require_once VENDOR_PATH . 'qiniu/php-sdk/autoload.php';
                // 需要填写你的 Access Key 和 Secret Key
                $accessKey = $config['accessKey'];
                $secretKey = $config['secretKey'];
                // 构建鉴权对象
                $auth = new Auth($accessKey,$secretKey);
                // 要上传的空间
                $bucket = $config['bucket'];
                $token = $auth->uploadToken($bucket);
                $this->assign('token', $token);
                return $this->fetch(":webuploader2");
            }else{
                return $this->fetch(":webuploader");
            }


        }
    }

    /**
     * @title 获取七牛云TOKEN
     * @description 获取七牛云TOKEN
     * @author Tiger Yang
     * @url /home/public/getQiNiuToken
     * @method GET
     *
     */
    public function getQiNiuToken(){
        $plugin=Db::name('plugin')->field('config')->where(['name'=>'Qiniu'])->find();
        $config=json_decode($plugin['config'],true);

        require_once VENDOR_PATH . 'qiniu/php-sdk/autoload.php';
        // 需要填写你的 Access Key 和 Secret Key
        $accessKey = $config['accessKey'];
        $secretKey = $config['secretKey'];
        // 构建鉴权对象
        $auth = new Auth($accessKey,$secretKey);
        // 要上传的空间
        $bucket = $config['bucket'];
        $token = $auth->uploadToken($bucket);
        echo $token;
    }

}
