<?php

class GroupService extends CI_Model
{


    public function __construct()
    {
        parent::__construct();

    }

    public function getAllGroupsCount()
    {
        $this->load->model('dao/GroupDao');

        $count = $this->GroupDao->getCountOfAll();

        return $count['total'];
    }

    public function getAllGroupIds()
    {
        $this->load->model('dao/GroupDao');

        $ret = $this->GroupDao->getAllGroupIds();

        return $ret;
    }

    //缓存
    public function getByWxGid($wxGid)
    {
        $cacheKey = 'GroupService_getByWxGid' . $wxGid;
        //缓存
        $this->load->model('redis/CacheRedis');
        $group = $this->CacheRedis->getK($cacheKey);
        if ($group != false) {
            return $group;
        }

        $this->load->model('dao/GroupDao');
        if ($wxGid == null) {
            throw new StatusException(Status::$message[Status::GROUP_NOT_EXIST], Status::GROUP_NOT_EXIST);
        }

        $group = $this->GroupDao->getOneByWxGid($wxGid);

        //设置缓存
        $this->CacheRedis->setK($cacheKey, $group);

        return $group;
    }

    //缓存
    public function getByGroupIds($groupIds)
    {
        $cacheKey = 'GroupService_getByGroupIds' . serialize($groupIds);
        //缓存
        $this->load->model('redis/CacheRedis');
        $groups = $this->CacheRedis->getK($cacheKey);
        if ($groups != false) {
            return $groups;
        }

        $this->load->model('dao/GroupDao');
        if (!is_array($groupIds) || empty($groupIds)) {
            throw new StatusException(Status::$message[Status::GROUP_NOT_EXIST], Status::GROUP_NOT_EXIST);
        }

        $groups = $this->GroupDao->getListByGroupIds($groupIds);
        if (empty($groups)) {
            throw new StatusException(Status::$message[Status::GROUP_NOT_EXIST], Status::GROUP_NOT_EXIST);
        }

        //设置缓存
        $this->CacheRedis->setK($cacheKey, $groups);

        return $groups;
    }

    public function createNewGroup($wxGid)
    {
        $this->load->model('dao/GroupDao');
        $this->load->model('redis/IdGenRedis');

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

        return $this->GroupDao->updateByGroupId($group['group_id'], $group);
    }

    public function updateUserAndTripCount($groupId, $memberNum, $tripNum)
    {
        $this->load->model('dao/GroupDao');

        $groups = $this->GroupDao->getListByGroupIds(array($groupId));
        if (empty($groups)) {
            throw new StatusException(Status::$message[Status::GROUP_NOT_EXIST], Status::GROUP_NOT_EXIST);
        }
        $group = $groups[0];
        $group['member_num'] = $memberNum;
        $group['trip_num'] = $tripNum;

        return $this->GroupDao->updateByGroupId($group['group_id'], $group);
    }

    public function increaseMember($groupId, $group)
    {
        $this->load->model('redis/LockRedis');
        $this->LockRedis->lockK($groupId);

        try {
            $this->load->model('dao/GroupDao');
            $group['member_num'] = $group['member_num'] + 1;
            $ret = $this->GroupDao->updateByGroupId($groupId, $group);
            $this->LockRedis->unlockK($groupId);
            return $ret;
        } catch (Exception $e) {
            $this->LockRedis->unlockK($groupId);
            throw $e;
        }
    }

    public function decreaseMember($groupId, $group)
    {
        $this->load->model('redis/LockRedis');
        $this->LockRedis->lockK($groupId);

        try {
            $this->load->model('dao/GroupDao');
            $group['member_num'] = $group['member_num'] - 1;
            if ($group['member_num'] < 0) {
                $group['member_num'] = 0;
            }
            $ret = $this->GroupDao->updateByGroupId($groupId, $group);
            $this->LockRedis->unlockK($groupId);
            return $ret;
        } catch (Exception $e) {
            $this->LockRedis->unlockK($groupId);
            throw $e;
        }
    }

    public function increaseTripInGroup($groupIds)
    {
        $this->load->model('redis/LockRedis');

        $groups = $this->getByGroupIds($groupIds);
        $updateGroups = array();
        foreach ($groups as $group) {
            $groupId = $group['group_id'];

            $this->LockRedis->lockK($groupId);
            try {
                $group['trip_num'] = $group['trip_num'] + 1;
                $updateGroups[] = $group;
                $this->GroupDao->updateByGroupId($groupId, $group);
                $this->LockRedis->unlockK($groupId);
            } catch (Exception $e) {
                $this->LockRedis->unlockK($groupId);
                throw $e;
            }
        }

        return;
    }

    public function decreaseTripInGroups($groupIds)
    {
        $this->load->model('redis/LockRedis');

        $groups = $this->getByGroupIds($groupIds);
        $updateGroups = array();
        foreach ($groups as $group) {
            $groupId = $group['group_id'];

            $this->LockRedis->lockK($groupId);
            try {
                $group['trip_num'] = $group['trip_num'] - 1;
                if ($group['trip_num'] < 0) {
                    $group['trip_num'] = 0;
                }
                $updateGroups[] = $group;
                $this->GroupDao->updateByGroupId($groupId, $group);
                $this->LockRedis->unlockK($groupId);
            } catch (Exception $e) {
                $this->LockRedis->unlockK($groupId);
                throw $e;
            }
        }
        return;
    }

}
