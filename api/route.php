<?php
// +----------------------------------------------------------------------
// | bronet [ 以客户为中心 以奋斗者为本 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.bronet.cn All rights reserved.
// +----------------------------------------------------------------------
$apps = cmf_scan_dir(APP_PATH . '*', GLOB_ONLYDIR);

foreach ($apps as $app) {
    $routeFile = APP_PATH . $app . '/route.php';

    if (file_exists($routeFile)) {
        include_once $routeFile;
    }

}


return [
];