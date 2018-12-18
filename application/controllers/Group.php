<?php

defined('BASEPATH') OR exit('No direct script access allowed');


class Group extends Base
{

    public function __construct()
    {
        parent::__construct();

    }

    public function addUser()
    {
        $input = $this->input->post();
        $user = $this->_user;
        $userId = $user['user_id'];


        $this->load->model('api/WxApi');
        $encryptedData = $input['encryptedData'];
        $iv = $input['iv'];
        $sessionKey = $user['wx_session_key'];
        $groupInfo = $this->WxApi->decryptGroupInfo($sessionKey, $encryptedData, $iv);
        $wxGid = $groupInfo['openGId'];

        //先检查群是否存在，如果不存在则创建群，最终获取group_id
        $this->load->model('service/GroupService');
        $group = $this->GroupService->getByWxGid($wxGid);
        if (empty($group)) {
            //如果不存在群组，则创建member_num为0的新群组
            $group = $this->GroupService->createNewGroup($wxGid);
        }

        //把人加到群里
        $this->load->model('service/GroupUserService');
        $ret = $this->GroupUserService->add($userId, $group['group_id'], $wxGid);
        if ($ret) {
            //用户是第一次加入群，需要把group的member_num加1
            $this->GroupService->increaseMember($group['group_id'], $group);
        }

        $this->_returnSuccess(null);
    }

    public function getDetailByGroupId()
    {
        $input = $this->input->post();
        $groupId = $input['group_id'];
        $this->load->model('service/GroupService');
        $group = $this->GroupService->getByGroupIds(array($groupId));

        $this->_returnSuccess($group);
    }

    public function updateNotice()
    {
        $input = $this->input->post();
        $user = $this->_user;
        $userId = $user['user_id'];
        $groupId = $input['group_id'];
        $notice = $input['notice'];

        //先检查是否为群主
        $this->load->model('service/GroupService');
        $groups = $this->GroupService->getByGroupIds(array($groupId));
        if ($groups[0]['owner_user_id'] != $userId) {
            throw new StatusException(Status::$message[Status::GROUP_NO_AUTH_UPDATE_NOTICE], Status::GROUP_NO_AUTH_UPDATE_NOTICE);
        }

        $ret = $this->GroupService->updateNotice($groupId, $notice);

        $this->_returnSuccess($ret);
    }

    public function getListByUserId()
    {
        $user = $this->_user;
        $userId = $user['user_id'];

        //获取群列表
        $this->load->model('service/GroupUserService');
        $groups = $this->GroupUserService->getGroupsByUserId($userId);
        $groupIds = array();
        if (!empty($groups)) {
            foreach ($groups as $group) {
                $groupIds[] = $group['group_id'];
            }
        }

        if (empty($groupIds)) {
            $this->_returnSuccess(array());
        }

        //获取列表内群详情
        $this->load->model('service/GroupService');
        $groupDetails = $this->GroupService->getByGroupIds($groupIds);


        $this->_returnSuccess($groupDetails);
    }


    public function topOneTrip()
    {
        $input = $this->input->post();
        $user = $this->_user;
        $userId = $user['user_id'];
        $groupId = $input['group_id'];
        $tripId = $input['trip_id'];

        //先检查是否为群主
        $this->load->model('service/GroupService');
        $groups = $this->GroupService->getByGroupIds(array($groupId));
        if ($groups[0]['owner_user_id'] != $userId) {
            throw new StatusException(Status::$message[Status::GROUP_NO_AUTH_UPDATE_NOTICE], Status::GROUP_NO_AUTH_UPDATE_NOTICE);
        }

        $this->load->model('service/GroupTripService');
        $ret = $this->GroupTripService->topOneTrip($groupId, $tripId);

        $this->_returnSuccess($ret);
    }


    public function unTopOneTrip()
    {
        $input = $this->input->post();
        $user = $this->_user;
        $userId = $user['user_id'];
        $groupId = $input['group_id'];
        $tripId = $input['trip_id'];

        //先检查是否为群主
        $this->load->model('service/GroupService');
        $groups = $this->GroupService->getByGroupIds(array($groupId));
        if ($groups[0]['owner_user_id'] != $userId) {
            throw new StatusException(Status::$message[Status::GROUP_NO_AUTH_UPDATE_NOTICE], Status::GROUP_NO_AUTH_UPDATE_NOTICE);
        }

        $this->load->model('service/GroupTripService');
        $ret = $this->GroupTripService->unTopOneTrip($groupId, $tripId);

        $this->_returnSuccess($ret);
    }

    public function exitGroup()
    {
        $input = $this->input->post();
        $user = $this->_user;
        $userId = $user['user_id'];
        $groupId = $input['group_id'];
        $this->load->model('service/GroupUserService');

        //确保群内有该用户
        $this->GroupUserService->ensureUserBelongToGroup($userId, $groupId);

        //先检查是否为群主，群主无法退群
        $this->load->model('service/GroupService');
        $groups = $this->GroupService->getByGroupIds(array($groupId));
        $group = $groups[0];
        if ($group['owner_user_id'] == $userId) {
            throw new StatusException(Status::$message[Status::GROUP_OWNER_CAN_NOT_EXIT], Status::GROUP_OWNER_CAN_NOT_EXIT);
        }

        $ret = $this->GroupUserService->delete($userId, $groupId);
        if ($ret) {
            //需要把group的member_num减少1
            $this->GroupService->decreaseMember($group['group_id'], $group);
        }
        $this->_returnSuccess($ret);
    }
}
