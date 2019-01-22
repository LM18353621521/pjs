<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/22
 * Time: 21:40
 */
use think\Config;
use think\Db;
use think\Url;
use dir\Dir;
use think\Route;
use think\Loader;
use think\Request;
use cmf\lib\Storage;


/**
 * 管理员操作记录
 * @param $log_info string 记录信息
 */
function adminLog($log_info){
    $add['log_time'] = time();
    $add['admin_id'] = session('ADMIN_ID');
    $add['log_info'] = $log_info;
    $add['log_ip'] = request()->ip();
    $add['log_url'] = request()->baseUrl() ;
    Db::name('admin_log')->insert($add);
}


