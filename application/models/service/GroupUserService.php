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
        if (empty($ret)) {
            throw new StatusException(Status::$message[Status::GROUP_USER_INVALID], Status::GROUP_USER_INVALID);
        }

        return;
    }

    public function getGroupsByUserId($userId)
    {
        $this->load->model('dao/GroupUserDao');
        if ($userId == null) {
            throw new StatusException(Status::$message[Status::GROUP_USER_INVALID], Status::GROUP_USER_INVALID);
        }

        $ret = $this->GroupUserDao->getGroupsByUserId($userId);
        if (empty($ret)) {
            return array();
        } else {
            $groups = array();
            foreach ($ret as $v) {
                $groups[] = array('group_id' => $v['group_id'], 'wx_gid' => $v['wx_gid']);
            }
            return $groups;
        }
    }

    public function add($userId, $groupId, $wxGid)
    {
        $this->load->model('dao/GroupUserDao');
        if ($userId == null || $groupId == null) {
            throw new StatusException(Status::$message[Status::GROUP_USER_INVALID], Status::GROUP_USER_INVALID);
        }

        $ret = $this->GroupUserDao->getOneByGroupIdAndUserId($userId, $groupId);
        if (empty($ret) || !is_array($ret) || count($ret) == 0) {
            $this->GroupUserDao->insertOne($userId, $groupId, $wxGid);
            return true;
        }

        return false;
    }

    public function delete($userId, $groupId)
    {
        $this->load->model('dao/GroupUserDao');
        if ($userId == null || $groupId == null) {
            throw new StatusException(Status::$message[Status::GROUP_USER_INVALID], Status::GROUP_USER_INVALID);
        }

        return $this->GroupUserDao->deleteOne($userId, $groupId);
    }
}
