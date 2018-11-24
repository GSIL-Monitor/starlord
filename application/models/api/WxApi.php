<?php

class WxApi extends CI_Model
{
    const APPID = 'wx1f9fb9bce77cd0e4';
    const SECRET = '993c780c8cdf242ce477cb50580ea381';

    public function getSessionKeyAndOpenId($code)
    {
        $data = array(
            'appid' => self::APPID,
            'secret' => self::SECRET,
            'js_code' => $code,
            'grant_type' => 'authorization_code'
        );

        $ret = Curl::get('https://api.weixin.qq.com/sns/jscode2session?', $data);

        $session = json_decode($ret, true);

        $openId = $session['openid'];
        $sessionKey = $session['session_key'];
        $errCode = $session['errcode'];
        $errMsg = $session['errmsg'];

        if($errCode != 0){
            throw new StatusException(Status::$message[Status::WX_FETCH_SESSION_FAIL], Status::WX_FETCH_SESSION_FAIL, $errMsg);
        }

        return array(
            'open_id' => $openId,
            'session_key' => $sessionKey,
        );

    }
}
