<?php
function returnFail(Exception $e){
    header("Content-Type: text/json");

    if($e instanceof StatusException){
        log_fatal("StatusException: errno=%s, errmsg=%s,cause=%s,trace=%s",$e->getCode(),$e->getMessage(),$e->getCause(),$e->getTraceAsString());
        echo json_encode(array('errno' => $e->getCode(), 'errmsg' => $e->getMessage(), 'data' => (object) null));
    }else{
        log_fatal("StatusException: errno=%s, errmsg=%s,trace=%s",$e->getCode(),$e->getMessage(),$e->getTraceAsString());
        echo json_encode(array('errno' => $e->getCode(), 'errmsg' => $e->getMessage(), 'data' => (object) null));
    }

    exit;
}
