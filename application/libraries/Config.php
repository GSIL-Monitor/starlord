<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Config
{

    //用户状态
    const USER_STATUS_OK = 0;
    const USER_STATUS_FROZEN = 1;

    //用户补全资料状态
    const USER_AUDIT_STATUS_OK = 0;
    const USER_AUDIT_STATUS_FAIL = 1;

    //用户查看安全协议
    const USER_HAS_READ = 0;
    const USER_HAS_NOT_READ = 1;

    //开始业务
    const USER_REG_IS_VALID = 0;
    const USER_REG_IS_INVALID = 1;

    //IDGEN的appkey
    const ID_GEN_KEY_USER = 'user_';
    const ID_GEN_KEY_TRIP = 'trip_';
    const ID_GEN_KEY_GROUP = 'group_';

    //数据库记录
    const RECORD_EXISTS = 0;
    const RECORD_DELETED = 1;

    //行程类型
    const TRIP_TYPE_DRIVER = 0;
    const TRIP_TYPE_PASSENGER = 1;

    //每天类型的行程的date常量
    const EVERYDAY_DATE = '2031-11-23';

    //行程状态
    const TRIP_STATUS_DRAFT = 0;
    const TRIP_STATUS_NORMAL = 1;
    const TRIP_STATUS_CANCEL = 2;

    //群内行程状态
    const GROUP_TRIP_STATUS_DEFAULT = 0;

    //群状态
    const GROUP_STATUS_DEFAULT = 0;

    //群成员状态
    const GROUP_USER_STATUS_DEFAULT = 0;


}
