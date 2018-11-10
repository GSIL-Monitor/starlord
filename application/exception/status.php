<?php
class Status
{
    const SUCCESS = 0;

    //validation
    const VALIDATION_IS_NULL = 10001;
    const VALIDATION_IS_NOT_NULL = 10002;
    const VALIDATION_EQUAL = 10003;
    const VALIDATION_NOT_EQUAL = 10004;
    const VALIDATION_NOT_TRUE = 10005;
    const VALIDATION_NOT_FALSE = 10006;
    const VALIDATION_GREATER_OR_EQUAL = 10007;
    const VALIDATION_ARRAY = 10008;

    //dao
    const DAO_INSERT_FAIL = 11001;
    const DAO_FETCH_FAIL = 11002;
    const DAO_MISS_FIELD = 11003;
    const DAO_MORE_THAN_ONE_RECORD = 11004;
    const DAO_UPDATE_WITHOUT_CONDITION = 11005;
    const DAO_UPDATE_FAIL = 11006;
    const DAO_HAS_NO_SHARD_KEY = 11007;

    //rpc
    const RPC_CALL_FAIL = 12001;
    const REQUEST_SIGN_ERROR = 12002;


    //redis
    const REDIS_HAS_NO_METHOD = 13001;
    const REDIS_EXECUTE_ERROR = 13002;
    const REDIS_LOCK_WAIT_TIMEOUT = 13003;
    const REDIS_CONNECT_ERROR = 13004;

    //parameter error
    const PARAM_ERROR = 14001;

    static public $message = array(
        self::SUCCESS => '',

        //validation
        self::VALIDATION_IS_NULL => '值为空',
        self::VALIDATION_IS_NOT_NULL => '值不为空',
        self::VALIDATION_EQUAL => '两个值相等',
        self::VALIDATION_NOT_EQUAL => '两个值不相等',
        self::VALIDATION_NOT_TRUE => '值不为TRUE',
        self::VALIDATION_NOT_FALSE => '值不为FALSE',
        self::VALIDATION_GREATER_OR_EQUAL => "值应该大于等于目标值",
        self::VALIDATION_ARRAY => '值应该是数组',

        //dao
        self::DAO_INSERT_FAIL => '数据库插入失败',
        self::DAO_FETCH_FAIL => '数据库读取失败',
        self::DAO_MISS_FIELD => '缺少数据库字段',
        self::DAO_MORE_THAN_ONE_RECORD => '返回多于一条记录',
        self::DAO_UPDATE_WITHOUT_CONDITION => '禁止不带条件update数据库',
        self::DAO_UPDATE_FAIL => '数据库更新失败',
        self::DAO_HAS_NO_SHARD_KEY => '没有分表key',

        //rpc
        self::RPC_CALL_FAIL => 'curl异常失败',
        self::REQUEST_SIGN_ERROR => '请求签名验证失败',

        //redis
        self::REDIS_HAS_NO_METHOD => 'redis方法不存在',
        self::REDIS_EXECUTE_ERROR => 'redis执行错误',
        self::REDIS_LOCK_WAIT_TIMEOUT => '等待锁超时',
        self::REDIS_CONNECT_ERROR => 'redis连接错误',

        //parameter error
        self::PARAM_ERROR => "请求参数错误",
    );
}
