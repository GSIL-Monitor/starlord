<?php
function returnFail(Exception $e)
{
    header("Content-Type: text/json");

    if ($e instanceof StatusException) {
        //request_error_log
        $logDate = date("Y-m-d H:i:s", time());
        $logContent = $logDate . ' | ' . 'request_error' . ' | ' . Base::$traceId . ' | ' . 'errno=' . $e->getCode() . ' | ' . 'errmsg=' . $e->getMessage();
        error_log($logContent . "\n", 3, Config::BIZ_ERROR_LOG_PATH);

        echo json_encode(array('errno' => $e->getCode(), 'errmsg' => $e->getMessage(), 'data' => (object)null));
    } else {
        //request_error_log
        $logDate = date("Y-m-d H:i:s", time());
        $logContent = $logDate . ' | ' . 'request_error' . ' | ' . Base::$traceId . ' | ' . 'errno=' . $e->getCode() . ' | ' . 'errmsg=' . $e->getMessage();
        error_log($logContent . "\n", 3, Config::BIZ_ERROR_LOG_PATH);

        echo json_encode(array('errno' => $e->getCode(), 'errmsg' => $e->getMessage(), 'data' => (object)null));
    }

    exit;
}
