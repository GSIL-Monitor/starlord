<?php

class CacheRedis extends CommonRedis
{
    const CACHE_EXPIRE_SECONDS = 60;
    const CACHE_PREFIX = 'STARLORD_CACHE_';

    public function __construct(){
        parent::__construct();
    }

    public function getValue($sKey){
        $sCacheKey = self::CACHE_PREFIX . $sKey;
        return $this->get($sCacheKey);
    }

    public function setValue($sKey, $sValue){
        $sCacheKey = self::CACHE_PREFIX . $sKey;
        return $this->setEx($sCacheKey, self::CACHE_EXPIRE_SECONDS, $sValue);
    }

}
