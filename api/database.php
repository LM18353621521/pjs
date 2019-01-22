<?php
// +----------------------------------------------------------------------
// | bronet [ 以客户为中心 以奋斗者为本 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.bronet.cn All rights reserved.
// +----------------------------------------------------------------------

if(file_exists(ROOT_PATH."data/conf/database.php")){
    $database=include ROOT_PATH."data/conf/database.php";
}else{
    $database=[];
}

return $database;
