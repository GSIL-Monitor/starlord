<?php
spl_autoload_register(function ($class) {
    $map = array(
        'Base' => APPPATH . "controllers/Base.php",
        'CommonRedis' => APPPATH . "models/redis/CommonRedis.php",
        'DbTansactionHanlder' => APPPATH . "models/transaction/DbTansactionHanlder.php",
        'Status' => APPPATH . "exception/Status.php",
        'StatusException' => APPPATH . "exception/StatusException.php",
        'Curl' => APPPATH . "libraries/Curl.php",
        'Config' => APPPATH . "libraries/Config.php",
        'TripDriverDetail' => APPPATH . "models/object/TripDriverDetail.php",
        'TripPassengerDetail' => APPPATH . "models/object/TripPassengerDetail.php",
        'TripDao' => APPPATH . "models/dao/TripDao.php",
    );

    if (isset($map[$class]) && file_exists($map[$class])) {
        include_once $map[$class];
    }
});
