<?php

defined('BASEPATH') OR exit('No direct script access allowed');


class User extends Base
{

    public function __construct()
    {
        parent::__construct();

    }

    public function config()
    {
        $this->load->model('service/TripDriverService');
        $this->load->model('service/TripPassengerService');
        $this->load->model('service/GroupService');

        $totalTripNum = $this->TripDriverService->getAllTripsCount() + $this->TripPassengerService->getAllTripsCount();
        $totalGroupNum = $this->GroupService->getAllGroupsCount();
        $user = $this->_user;
        $config = array(
            'expire' => 3600,
            'cert' => '!@#QWE!@#Dvvdfsvf',
            'docoment' => array(
                'share_description' => '通过管家发布行程到本群，拼车更高效，点击查看详情',            //分享小卡片上的描述语句
                'notice_list' => array(
                    '在拼车群中点击任意【拼车群管家】发布的行程，即可加入管理列表。',
                    '通过管家发布行程到你的拼车群，同群拼友可以在列表看到你的行程。',
                ),                //拼车群tab上的公告
                'adopt' => '如果您是本群群主，请加客服（微信号：pinchequnguanjia）,成为该群管理员；<br />ps：群主同意的情况下，其他成员也可以担任管理员。<br />管理员福利：发布公告，置顶群内行程，微信群拉新~更多功能开发中',        //没有群主的群的认领文案
                'faq' => '关于我们',                //没有拼车群的时候展现的使用说明，同时也是”我的“中”关于我们“的内容
                'platform_info' => '目前共有' . $totalGroupNum . '个群使用【拼车群管家】管理，共有' . $totalTripNum . '个有效行程',    //搜索页上方的平台信息，说明现在平台有x个微信群，y个行程
                'search_tip' => '可以搜索到所有通过【拼车群管家】发布到拼车群的行程',        //搜索页搜索按钮下面的提示信息
                'group_owner_info' => '在拼车群内点击任一拼车群管家的分享行程，即可在我的拼车群列表中添加该群',        //群主信息
                'publish_tip' => '发布行程到你的拼车群，同群好友快速查询，其他群的拼友也可以搜到你的行程',        //发布首页下面的说明文字
                'publish_finish_tip' => '已发布的行程，在【我的】->【我的行程】可以查看、删除、分享',        //发布完成返回按钮下的文案
                'share_tip' => '如果发布的拼车群还未加入管理，在群内点击你发布的行程，将拼车群加入管理',    //发布到拼车群按钮下的文案
                'car_tip' => '发布行程时自动显示的车辆信息',        //我的车辆编辑页面保存按钮下的文案
                'user_tip' => '发布行程时自动显示的手机号',    //我的资料编辑页面保存按钮下的文案
                'contact' => '客服微信：pinchequnguanjia',        //联系客服页的内容
            ),
            'switch' => array(
                '9999' => ($user['is_valid'] == Config::USER_REG_IS_INVALID),                    //正常进入页面功能or展示维护公告
                '9999_context' => "维护中，预计12：00开放使用，非常抱歉。",                    //正常进入页面功能or展示维护公告
                'search_tag' => 0,                //搜索页是否展现标签选择
                'search_all_group' => 1,            //搜索页展示是否跨群选项or写死文案只能群内搜索
                'show_agreement' => $user['show_agreement'],        //是否展示安全协议，当读完安全协议后，需要在服务端user表内和本地都置为否
                'trip_publish_to_all_group' => 0,        //如果非空，值为发布选择群上面的提示文案，如果为空发布时候不弹出选择群
            ),
        );

        $this->_returnSuccess($config);
    }

    public function getProfile()
    {
        $user = $this->_user;
        $showUser = array();
        $showUser["phone"] = $user["phone"];
        $showUser["nick_name"] = $user["nick_name"];
        $showUser["gender"] = $user["gender"];
        $showUser["city"] = $user["city"];
        $showUser["province"] = $user["province"];
        $showUser["country"] = $user["country"];
        $showUser["avatar_url"] = $user["avatar_url"];
        $showUser["car_plate"] = $user["car_plate"];
        $showUser["car_brand"] = $user["car_brand"];
        $showUser["car_model"] = $user["car_model"];
        $showUser["car_color"] = $user["car_color"];
        $showUser["car_type"] = $user["car_type"];
        $showUser["audit_status"] = $user["audit_status"];
        $showUser["show_agreement"] = $user["show_agreement"];

        $this->_returnSuccess($showUser);
    }

    public function completeUser()
    {
        $input = $this->input->post();
        $user = $this->_user;
        $this->load->model('service/UserService');
        $this->load->model('api/WxApi');

        $rawData = $input['rawData'];
        $signature = $input['signature'];
        $encryptedData = $input['encryptedData'];
        $iv = $input['iv'];
        $sessionKey = $user['wx_session_key'];

        if ($signature != sha1($rawData . $sessionKey)) {
            throw new StatusException(Status::$message[Status::WX_DECRYPT_ERROR], Status::WX_DECRYPT_ERROR);
        }

        $userInfo = $this->WxApi->decryptUserInfo($sessionKey, $encryptedData, $iv);

        $user['wx_union_id'] = $userInfo['unionId'];
        $user['nick_name'] = $userInfo['nickName'];
        $user['gender'] = $userInfo['gender'];
        $user['city'] = $userInfo['city'];
        $user['province'] = $userInfo['province'];
        $user['country'] = $userInfo['country'];
        $user['avatar_url'] = $userInfo['avatarUrl'];
        $user['audit_status'] = Config::USER_AUDIT_STATUS_OK;

        $ret = $this->UserService->updateUser($user);
        $this->_returnSuccess($ret);
    }

    public function updateUserCar()
    {
        $input = $this->input->post();
        $user = $this->_user;
        $this->load->model('service/UserService');

        $user['car_plate'] = $input['car_plate'];
        $user['car_brand'] = $input['car_brand'];
        $user['car_model'] = $input['car_model'];
        $user['car_color'] = $input['car_color'];
        $user['car_type'] = $input['car_type'];

        $ret = $this->UserService->updateUser($user);
        $this->_returnSuccess($ret);
    }

    public function updateUserAgreement()
    {
        $user = $this->_user;
        $this->load->model('service/UserService');

        $user['show_agreement'] = Config::USER_HAS_READ;

        $ret = $this->UserService->updateUser($user);
        $this->_returnSuccess($ret);
    }

    public function updateUserPhone()
    {
        $input = $this->input->post();
        $user = $this->_user;
        $this->load->model('service/UserService');

        $user['phone'] = $input['phone'];
        $this->_checkPhone($user['phone']);

        $ret = $this->UserService->updateUser($user);
        $this->_returnSuccess($ret);

    }

    private function _checkPhone($phone)
    {
        $preg = '/^1\d{10}$/ims';

        if (!preg_match($preg, $phone)) {
            throw new StatusException(Status::$message[Status::USER_PHONE_INVALID], Status::USER_PHONE_INVALID);
        }
    }
}

