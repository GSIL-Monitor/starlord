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
        $wxGid = $input['wx_gid'];

        //先检查群是否存在，如果不存在则创建群，最终获取group_id
        $this->load->model('service/GroupService');
        $group = $this->GroupService->getByWxGid($wxGid);
        if (empty($group)) {
            $group = $this->GroupService->createNewGroup($wxGid);
        }

        //把人加到群里
        $this->load->model('service/GroupUserService');
        $this->GroupUserService->add($userId, $group['group_id']);

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
        $input = $this->input->post();
        $user = $this->_user;
        $userId = $user['user_id'];

        //获取群列表
        $this->load->model('service/GroupUserService');
        $groupIds = $this->GroupUserService->getGroupIdsByUserId($userId);

        if (empty($groupIds)) {
            $this->_returnSuccess(array());
        }

        //获取列表内群详情
        $this->load->model('service/GroupService');
        $groups = $this->GroupService->getByGroupIds($groupIds);

        $this->_returnSuccess($groups);
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


    public
    function unTopOneTrip()
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

}