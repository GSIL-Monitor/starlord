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

        if (isset($session['errcode']) && $session['errcode'] != 0) {
            throw new StatusException(Status::$message[Status::WX_FETCH_SESSION_FAIL], Status::WX_FETCH_SESSION_FAIL, $session['errmsg']);
        } else {
            return array(
                'open_id' => $session['openid'],
                'session_key' => $session['session_key'],
            );
        }
    }

    public function decryptUserInfo($sessionKey, $encryptedData, $iv)
    {
        if (strlen($sessionKey) != 24) {
            throw new StatusException(Status::$message[Status::WX_DECRYPT_ERROR], Status::WX_DECRYPT_ERROR);
        }
        $aesKey = base64_decode($sessionKey);

        if (strlen($iv) != 24) {
            throw new StatusException(Status::$message[Status::WX_DECRYPT_ERROR], Status::WX_DECRYPT_ERROR);
        }

        $aesIV = base64_decode($iv);
        $aesCipher = base64_decode($encryptedData);
        $result = openssl_decrypt($aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);
        $data = json_decode($result, true);
        if ($data == NULL) {
            throw new StatusException(Status::$message[Status::WX_DECRYPT_ERROR], Status::WX_DECRYPT_ERROR);
        }
        if ($data->watermark->appid != self::APPID) {
            throw new StatusException(Status::$message[Status::WX_DECRYPT_ERROR], Status::WX_DECRYPT_ERROR);
        }
        return $data;
    }
}
