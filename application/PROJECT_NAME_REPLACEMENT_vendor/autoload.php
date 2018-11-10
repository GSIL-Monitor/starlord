<?php
spl_autoload_register(function($class){
    $map = array(
        'BaseController' => APPPATH . "controllers/baseController.php",
        'CommonDao' => APPPATH . "models/dao/commonDao.php",
        'CommonRedis' => APPPATH . "models/redis/commonRedis.php",
        'DbTansactionHanlder' => APPPATH . "models/transaction/dbTansactionHanlder.php",
        'Status' => APPPATH . "exception/status.php",
        'StatusException' => APPPATH . "exception/statusException.php",
    );
    if (isset($map[$class]) && file_exists($map[$class])) {
        include_once $map[$class];
    }
});
