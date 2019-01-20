<?php

class OssApi extends CI_Model
{
    const END_POINT_EXTERNAL = 'oss-cn-beijing.aliyuncs.com';
    const END_POINT_INTERNAL = 'oss-cn-beijing-internal.aliyuncs.com';
    const ACCESS_KEY = 'LTAI7FqpQmEvcZi7';
    const ACCESS_SECRET = 'htoTg4N78pkeliph79q8SfrnDfgO96';
    const BUCKET = 'starlord-trip-prod';

    protected static $ossClient = array();

    private function _getOssClient($endPoint)
    {
        if (empty(self::$ossClient[$endPoint])) {
            try {
                self::$ossClient[$endPoint] = new OSS\OssClient(self::ACCESS_KEY, self::ACCESS_SECRET, $endPoint, false);
            } catch (OSS\Core\OssException $e) {
                throw new StatusException(Status::$message[Status::OSS_CONNECT_FAIL], Status::OSS_CONNECT_FAIL, json_encode($e));
            }
        }

        return self::$ossClient[$endPoint];
    }


    public function uploadImg($object, $filePath)
    {
        try {
            $ossClient = $this->_getOssClient(self::END_POINT_INTERNAL);
            $ossClient->uploadFile(self::BUCKET, $object, $filePath);
        } catch (OSS\Core\OssException $e) {
            throw new StatusException(Status::$message[Status::OSS_CONNECT_FAIL], Status::OSS_CONNECT_FAIL, json_encode($e));
        }
    }

    public function getSignedUrlForGettingObject($tripId)
    {
        return 'http://img.pinche.mobi/prod/' . $tripId . '.png';
    }

}

