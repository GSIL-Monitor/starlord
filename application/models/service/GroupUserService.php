<?php

class GroupUserService extends CI_Model
{


    public function __construct()
    {
        parent::__construct();

    }

    public function ensureUserBelongToGroup($userId, $groupId)
    {
        $this->load->model('dao/GroupUserDao');
        if ($userId == null || $groupId == null) {
            throw new StatusException(Status::$message[Status::GROUP_EXCLUDE_USER], Status::GROUP_EXCLUDE_USER);
        }

        $ret = $this->GroupUserDao->getOneByGroupIdAndUserId($userId, $groupId);
        ensureNotNull($ret);

        return;
    }

    public function getGroupIdsByUserId($userId)
    {
        $this->load->model('dao/GroupUserDao');
        if ($userId == null) {
            throw new StatusException(Status::$message[Status::GROUP_USER_INVALID], Status::GROUP_USER_INVALID);
        }

        $ret = $this->GroupUserDao->getGroupsByUserId($userId);
        if (empty($ret)) {
            return array();
        } else {
            $groupIds = array();
            foreach ($ret as $v) {
                $groupIds[] = $v['group_id'];
            }
            return $groupIds;
        }
    }

    public function add($userId, $groupId)
    {
        $this->load->model('dao/GroupUserDao');
        if ($userId == null || $groupId == null) {
            throw new StatusException(Status::$message[Status::GROUP_USER_INVALID], Status::GROUP_USER_INVALID);
        }

        $ret = $this->GroupUserDao->getOneByGroupIdAndUserId($userId, $groupId);
        if (empty($ret) || !is_array($ret) || count($ret) == 0) {
            $this->GroupUserDao->insertOne($userId, $groupId);
        }

        return;
    }


}
