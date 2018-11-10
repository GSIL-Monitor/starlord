<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Curl {

    static private $isInit = false;
    static private $services = array();

    static public function init(){
        if (self::$isInit) {
            return;
        }

        get_instance()->config->load('curl');
        $services = get_config()['curl']['services'];
        foreach ($services as $key => $val) {
            self::$services[$key] = $val;
        }
        self::$isInit = true;
    }

    static public function get($service, $path, $params=array(),$options=array()) {
        $fStartTime = microtime(true);

        self::init();
        $conf = self::$services[$service];
        $ci = curl_init();
        $url = $conf['host'].$path.'?'.http_build_query($params);
        curl_setopt($ci, CURLOPT_URL, $url);
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $conf['connect_timeout']);
        curl_setopt($ci, CURLOPT_TIMEOUT, $conf['timeout']);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ci, CURLOPT_HEADER, false);
        foreach ($options as $k=>$v){
            curl_setopt($ci,$k,$v);
        }
        $logParams = array(
            'url' => $url,
            'service' => $service,
            'method' => 'get',
        );

        $response = curl_exec($ci);

        $fEndTime = microtime(true);
        $fProcTime = ($fEndTime - $fStartTime) * 1000;

        $msg = curl_error($ci);
        $code = curl_errno($ci);
        $logParams['curl_msg']=$msg;
        $logParams['curl_errno']=$code;
        log_notice("curl data: ".json_encode($logParams));
        if (!$response) {
            com_http_failure($url, $fProcTime, $code, $msg);
            return false;
        }
        com_http_success($url, $fProcTime, $response);
        if ($conf['format'] === 'json') {
            return json_decode($response, true);
        }
        return $response;
    }

    static public function  post($service, $path, $params=array(),$options=array()) {
        $fStartTime = microtime(true);

        self::init();
        $conf = self::$services[$service];
        $ci = curl_init();
        if (empty($conf['host'])) {
            $url = $path;
        } else {
            $url = $conf['host'].$path;
        }
        curl_setopt($ci, CURLOPT_URL, $url);
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $conf['connect_timeout']);
        curl_setopt($ci, CURLOPT_TIMEOUT, $conf['timeout']);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ci, CURLOPT_HEADER, false);
        curl_setopt($ci, CURLOPT_POST, TRUE);
        foreach ($options as $k=>$v){
            curl_setopt($ci,$k,$v);
        }
        if (is_string($params)) {
            $postfields = $params;
        } else {
            $postfields = http_build_query($params);
        }
        curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
        $logParams = array(
            'url' => $url,
            'service' => $service,
            'method' => 'post',
            'data' => $postfields,
        );
        $response = curl_exec($ci);

        $fEndTime = microtime(true);
        $fProcTime = ($fEndTime - $fStartTime) * 1000;

        $logParams['resp'] = $response;
        $msg = curl_error($ci);
        $code = curl_errno($ci);
        $logParams['curl_msg']=$msg;
        $logParams['curl_errno']=$code;
        log_notice("curl data: ".json_encode($logParams));
        if (!$response) {
            com_http_failure($url, $fProcTime, $code, $msg);
            return false;
        }
        com_http_success($url, $fProcTime, $response);
        if ($conf['format'] === 'json') {
            return json_decode($response, true);
        }
        return $response;
    }

    static public function getConf($service) {
        self::init();
        return self::$services[$service];
    }

    static public function getService($service) {
        self::init();
        $conf = self::$services[$service];
        return $conf;
    }
}
