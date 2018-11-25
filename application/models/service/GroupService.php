<?php

class GroupService extends CI_Model
{


    public function __construct()
    {
        parent::__construct();

    }

    public function getByWxGid($wxGid)
    {
        $this->load->model('dao/GroupDao');
        if ($wxGid == null) {
            throw new StatusException(Status::$message[Status::GROUP_NOT_EXIST], Status::GROUP_NOT_EXIST);
        }

        $group = $this->GroupDao->getOneByWxGid($wxGid);

        return $group;
    }

    public function getByGroupIds($groupIds)
    {
        $this->load->model('dao/GroupDao');
        if (!is_array($groupIds) || empty($groupIds)) {
            throw new StatusException(Status::$message[Status::GROUP_NOT_EXIST], Status::GROUP_NOT_EXIST);
        }

        $groups = $this->GroupDao->getListByGroupIds($groupIds);
        if (empty($groups)) {
            throw new StatusException(Status::$message[Status::GROUP_NOT_EXIST], Status::GROUP_NOT_EXIST);
        }

        return $groups;
    }

    public function createNewGroup($wxGid)
    {
        $this->load->model('dao/GroupDao');

        $group = array();
        $group['group_id'] = $this->IdGenRedis->gen(Config::ID_GEN_KEY_GROUP);
        $group['wx_gid'] = $wxGid;
        $group['member_num'] = 0;
        $group['trip_num'] = 0;
        $group['owner_user_id'] = 0;
        $group['owner_wx_id'] = "";
        $group['notice'] = "请更新公告";
        $group['status'] = Config::GROUP_STATUS_DEFAULT;

        $group = $this->GroupDao->insertOne($group['group_id'], $group);

        return $group;
    }

    public function updateNotice($groupId, $notice)
    {
        $this->load->model('dao/GroupDao');

        $groups = $this->GroupDao->getListByGroupIds(array($groupId));
        if (empty($groups)) {
            throw new StatusException(Status::$message[Status::GROUP_NOT_EXIST], Status::GROUP_NOT_EXIST);
        }
        $group = $groups[0];
        $group['notice'] = $notice;

        return $this->GroupDao->updateByGroupId($group);
    }
}
