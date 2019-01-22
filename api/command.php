<?php
// +----------------------------------------------------------------------
// | bronet [ 以客户为中心 以奋斗者为本 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.bronet.cn All rights reserved.
// +----------------------------------------------------------------------

$apps = cmf_scan_dir(APP_PATH . '*', GLOB_ONLYDIR);

$returnCommands = [];

foreach ($apps as $app) {
    $commandFile = APP_PATH . $app . '/command.php';

    if (file_exists($commandFile)) {
        $commands       = include $commandFile;

        $returnCommands = array_merge($returnCommands, $commands);
    }

}

return $returnCommands;