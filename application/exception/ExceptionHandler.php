<?php
function returnFail(Exception $e){
    header("Content-Type: text/json");

    if($e instanceof StatusException){
        echo json_encode(array('errno' => $e->getCode(), 'errmsg' => $e->getMessage(), 'data' => (object) null));
    }else{
        echo json_encode(array('errno' => $e->getCode(), 'errmsg' => $e->getMessage(), 'data' => (object) null));
    }

    exit;
}
