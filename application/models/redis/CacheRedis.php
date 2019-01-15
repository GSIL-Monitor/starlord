<?php

class CacheRedis extends CommonRedis
{
    const CACHE_EXPIRE_SECONDS = 6000;
    const CACHE_PREFIX = 'STARLORD_CACHE_';

    public function __construct()
    {
        parent::__construct();
    }

    public function getK($sKey)
    {
        $sCacheKey = self::CACHE_PREFIX . $sKey;
        return unserialize($this->get($sCacheKey));
    }

    public function delK($sKey)
    {
        $sCacheKey = self::CACHE_PREFIX . $sKey;
        return $this->delete($sCacheKey);
    }

    public function setKV($sKey, $sValue)
    {
        $sCacheKey = self::CACHE_PREFIX . $sKey;
        return $this->setEx($sCacheKey, self::CACHE_EXPIRE_SECONDS, serialize($sValue));
    }

}
