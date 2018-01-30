<?php
define("APP_PATH",  realpath(dirname(__FILE__) . '/../')); /* 指向public的上一级 */
$app  = new Yaf_Application(APP_PATH . "/conf/application.ini");
//导入一个函数库文件common.php，即可使用common.php中的函数
Yaf_Loader::import(APP_PATH.'/application/helpers/common.php');
$app
    ->bootstrap() //可选的调用
    ->run();
?>