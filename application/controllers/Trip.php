<?php

defined('BASEPATH') OR exit('No direct script access allowed');


class Trip extends Base
{

    public function __construct()
    {
        parent::__construct();

    }

    public function getTripDetailInSharePage()
    {
        $input = $this->input->post();
        $user = $this->_user;
        $userId = $user['user_id'];
        $tripUserId = $input['user_id'];
        $tripId = $input['trip_id'];
        $tripType = $input['trip_type'];


        $this->load->model('api/WxApi');
        $encryptedData = $input['encryptedData'];
        $iv = $input['iv'];
        $sessionKey = $user['wx_session_key'];
        $groupInfo = $this->WxApi->decryptGroupInfo($sessionKey, $encryptedData, $iv);
        $wxGid = $groupInfo['openGId'];

        //先检查群是否存在，如果不存在则创建群，最终获取group_id
        $this->load->model('service/GroupService');

        DbTansactionHanlder::begin('default');
        try {
            $group = $this->GroupService->getByWxGid($wxGid);
            if (empty($group)) {
                //如果不存在群组，则创建member_num为0的新群组
                $group = $this->GroupService->createNewGroup($wxGid);
            }

            $groupId = $group['group_id'];
            $this->load->model('service/GroupUserService');
            try {
                //检查人是否存在
                $this->GroupUserService->ensureUserBelongToGroup($userId, $groupId);
            } catch (StatusException $e) {
                //如果不存在
                $ret = $this->GroupUserService->add($userId, $groupId, $wxGid);
                if ($ret) {
                    //用户是第一次加入群，需要把group的member_num加1
                    $this->GroupService->increaseMember($groupId, $group);
                }
            }

            $trip = $this->_getDetailByTripId($tripType, $tripUserId, $tripId);
            $retTrip = $trip;
            $this->load->model('service/GroupTripService');
            try {
                //检查行程是否在群里

                $this->GroupTripService->ensureGroupHasTrip($groupId, $tripId);
            } catch (StatusException $e) {
                //否则把行程加群
                $this->load->model('service/GroupService');
                $this->GroupTripService->publishTripToGroup($tripId, $groupId, $trip, $tripType);
                $this->GroupService->increaseTripInGroup(array($groupId));

                unset($trip['is_expired']);
                if ($tripType == Config::TRIP_TYPE_DRIVER) {
                    $this->TripDriverService->addGroupInfoToTrip($tripUserId, $tripId, $trip, $group);
                } else {
                    $this->TripPassengerService->addGroupInfoToTrip($tripUserId, $tripId, $trip, $group);
                }
            }
            throw new StatusException(Status::$message[Status::TRIP_IS_NOT_TEMPLATE], Status::TRIP_IS_NOT_TEMPLATE);
            DbTansactionHanlder::commit('default');
            $this->_returnSuccess($retTrip);
        } catch (Exception $e) {
            DbTansactionHanlder::rollBack('default');
            throw $e;
        }
    }

    //--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
    //获取群内行程列表
    public function driverGetListByGroupId()
    {
        $this->_returnSuccess($this->_getListByGroupId(Config::TRIP_TYPE_DRIVER));
    }

    public function passengerGetListByGroupId()
    {
        $this->_returnSuccess($this->_getListByGroupId(Config::TRIP_TYPE_PASSENGER));

    }

    private function _sortTripsInGroup($trips)
    {
        $topTripsSortKeys = array();
        $topTrips = array();

        $restTripsSortKeys = array();
        $restTrips = array();

        foreach ($trips as $trip) {
            if (empty($trip['top_time'])) {
                $restTripsSortKeys[] = $trip['created_time'];
                $restTrips[] = $trip;
            } else {
                $topTripsSortKeys[] = $trip['top_time'];
                $topTrips[] = $trip;
            }
        }

        array_multisort($topTripsSortKeys, SORT_DESC, SORT_REGULAR, $topTrips);
        array_multisort($restTripsSortKeys, SORT_DESC, SORT_REGULAR, $restTrips);

        return array_merge($topTrips, $restTrips);
    }

    private function _getListByGroupId($tripType)
    {
        $this->load->model('service/TripPassengerService');
        $this->load->model('service/GroupTripService');
        $this->load->model('service/GroupUserService');

        $user = $this->_user;
        $userId = $user['user_id'];

        $input = $this->input->post();
        $groupId = $input['group_id'];
        if ($groupId == null) {
            throw new StatusException(Status::$message[Status::GROUP_NOT_EXIST], Status::GROUP_NOT_EXIST);
        }

        //确保群内有该用户
        $this->GroupUserService->ensureUserBelongToGroup($userId, $groupId);

        //获取当前date之后的trips
        $trips = $this->GroupTripService->getCurrentTripIdsByGroupIdAndTripType($groupId, $tripType);

        //格式化群内行程,按照createdtime排序，toptime置顶
        return $this->_sortTripsInGroup($trips);
    }

    //--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
    //获取行程详情
    public function driverGetDetailByTripId()
    {
        $input = $this->input->post();
        $userId = $input['user_id'];
        $tripId = $input['trip_id'];

        $this->_returnSuccess($this->_getDetailByTripId(Config::TRIP_TYPE_DRIVER, $userId, $tripId));
    }

    public function passengerGetDetailByTripId()
    {
        $input = $this->input->post();
        $userId = $input['user_id'];
        $tripId = $input['trip_id'];

        $this->_returnSuccess($this->_getDetailByTripId(Config::TRIP_TYPE_PASSENGER, $userId, $tripId));
    }

    private function _getDetailByTripId($tripType, $userId, $tripId)
    {

        if ($tripId == null) {
            throw new StatusException(Status::$message[Status::TRIP_NOT_EXIST], Status::TRIP_NOT_EXIST);
        }

        //无需鉴权，所有用户都能看行程详情，因为分享页需要
        $trip = null;
        if ($tripType == Config::TRIP_TYPE_DRIVER) {
            $this->load->model('service/TripDriverService');
            $trip = $this->TripDriverService->getTripByTripId($userId, $tripId);

        } else {
            $this->load->model('service/TripPassengerService');
            $trip = $this->TripPassengerService->getTripByTripId($userId, $tripId);
        }

        $currentDate = date('Y-m-d');
        if (isset($trip['begin_date']) && $currentDate > $trip['begin_date']) {
            $trip['is_expired'] = true;
        } else {
            $trip['is_expired'] = false;
        }

        return $trip;
    }
    //--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
    //保存行程到模板
    public function driverSave()
    {
        $input = $this->input->post();
        $user = $this->_user;
        $userId = $user['user_id'];
        $tripId = $input['trip_id'];

        $tripDriverDetail = new TripDriverDetail($input);

        $this->load->model('service/TripDriverService');

        DbTansactionHanlder::begin('default');
        try {
            $ret = $this->TripDriverService->saveTripTemplate($tripId, $userId, $tripDriverDetail->getTripArray());
            DbTansactionHanlder::commit('default');
            $this->_returnSuccess($ret);
        } catch (StatusException $e) {
            DbTansactionHanlder::rollBack('default');
            throw $e;
        }
    }

    public function passengerSave()
    {
        $input = $this->input->post();
        $user = $this->_user;
        $userId = $user['user_id'];
        $tripId = $input['trip_id'];

        $tripPassengerDetail = new TripPassengerDetail($input);

        $this->load->model('service/TripPassengerService');

        DbTansactionHanlder::begin('default');
        try {
            $ret = $this->TripPassengerService->saveTripTemplate($tripId, $userId, $tripPassengerDetail->getTripArray());
            DbTansactionHanlder::commit('default');
            $this->_returnSuccess($ret);
        } catch (StatusException $e) {
            DbTansactionHanlder::rollBack('default');
            throw $e;
        }

    }

    //--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
    //发布行程
    public function driverPublish()
    {
        $input = $this->input->post();
        $user = $this->_user;

        //没有手机号码的人无法发布行程
        if (empty($user['phone'])) {
            throw new StatusException(Status::$message[Status::TRIP_HAS_NO_AUTH_TO_PUBLISH], Status::TRIP_HAS_NO_AUTH_TO_PUBLISH);
        }

        $userId = $user['user_id'];
        $tripDriverDetail = new TripDriverDetail($input);

        $this->load->model('service/TripDriverService');

        if ($user['status'] == Config::USER_STATUS_FROZEN || $user['status'] == Config::USER_AUDIT_STATUS_FAIL) {
            throw new StatusException(Status::$message[Status::TRIP_HAS_NO_AUTH_TO_PUBLISH], Status::TRIP_HAS_NO_AUTH_TO_PUBLISH);
        }

        //发布到trip表
        $newTrip = $this->TripDriverService->createNewTrip($userId, $tripDriverDetail->getTripArray(), $this->_user);

        $newTrip['trip_id'] = $newTrip['trip_id'] . "";
        $this->_returnSuccess($newTrip);
    }

    public function passengerPublish()
    {
        $input = $this->input->post();
        $user = $this->_user;

        //没有手机号码的人无法发布行程
        if (empty($user['phone'])) {
            throw new StatusException(Status::$message[Status::TRIP_HAS_NO_AUTH_TO_PUBLISH], Status::TRIP_HAS_NO_AUTH_TO_PUBLISH);
        }

        $userId = $user['user_id'];

        $tripPassengerDetail = new TripPassengerDetail($input);

        $this->load->model('service/TripPassengerService');

        if ($user['status'] == Config::USER_STATUS_FROZEN || $user['status'] == Config::USER_AUDIT_STATUS_FAIL) {
            throw new StatusException(Status::$message[Status::TRIP_HAS_NO_AUTH_TO_PUBLISH], Status::TRIP_HAS_NO_AUTH_TO_PUBLISH);
        }
        //发布到trip表
        $newTrip = $this->TripPassengerService->createNewTrip($userId, $tripPassengerDetail->getTripArray(), $this->_user);

        $newTrip['trip_id'] = $newTrip['trip_id'] . "";
        $this->_returnSuccess($newTrip);
    }

    //--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
    //更新我的行程
    public function driverUpdateMy()
    {
        $input = $this->input->post();
        $user = $this->_user;
        $userId = $user['user_id'];
        $tripId = $input['trip_id'];

        $tripDriverDetail = new TripDriverDetail($input);

        $this->load->model('service/TripDriverService');
        $ret = $this->TripDriverService->updateTrip($tripId, $userId, $tripDriverDetail->getTripArray());

        $this->_returnSuccess($ret);
    }

    public function passengerUpdateMy()
    {
        $input = $this->input->post();
        $user = $this->_user;
        $userId = $user['user_id'];
        $tripId = $input['trip_id'];

        $tripPassengerDetail = new TripPassengerDetail($input);

        $this->load->model('service/TripPassengerService');

        $ret = $this->TripPassengerService->updateTrip($tripId, $userId, $tripPassengerDetail->getTripArray());

        $this->_returnSuccess($ret);
    }

    //--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
    //取消我的行程
    public function driverDeleteMy()
    {
        $input = $this->input->post();
        $user = $this->_user;
        $userId = $user['user_id'];
        $tripId = $input['trip_id'];

        $this->load->model('service/TripDriverService');
        $trip = $this->TripDriverService->getTripByTripId($userId, $tripId);
        //鉴权不过，无法删除
        if (empty($trip)) {
            throw new StatusException(Status::$message[Status::TRIP_NOT_EXIST], Status::TRIP_NOT_EXIST);
        }

        $this->TripDriverService->deleteTrip($userId, $tripId);

        //获取用户所在群的id
        $this->load->model('service/GroupUserService');
        $groups = $this->GroupUserService->getGroupsByUserId($userId);
        $groupIds = array();
        if (!empty($groups)) {
            foreach ($groups as $group) {
                $groupIds[] = $group['group_id'];
            }
        }
        if (!empty($groupIds)) {
            $this->load->model('service/GroupTripService');
            $this->load->model('service/GroupService');
            $this->GroupTripService->deleteTripsFromGroup($tripId);
            $this->GroupService->decreaseTripInGroups($groupIds);
        }

        $this->_returnSuccess(null);
    }

    public function passengerDeleteMy()
    {
        $input = $this->input->post();
        $user = $this->_user;
        $userId = $user['user_id'];
        $tripId = $input['trip_id'];

        $this->load->model('service/TripPassengerService');
        $trip = $this->TripPassengerService->getTripByTripId($userId, $tripId);
        //鉴权不过，无法删除
        if (empty($trip)) {
            throw new StatusException(Status::$message[Status::TRIP_NOT_EXIST], Status::TRIP_NOT_EXIST);
        }

        $this->TripPassengerService->deleteTrip($userId, $tripId);

        //获取用户所在群的id
        $this->load->model('service/GroupUserService');
        $groups = $this->GroupUserService->getGroupsByUserId($userId);
        $groupIds = array();
        if (!empty($groups)) {
            foreach ($groups as $group) {
                $groupIds[] = $group['group_id'];
            }
        }
        if (!empty($groupIds)) {
            $this->load->model('service/GroupTripService');
            $this->load->model('service/GroupService');
            $this->GroupTripService->deleteTripsFromGroup($tripId);
            $this->GroupService->decreaseTripInGroups($groupIds);
        }

        $this->_returnSuccess(null);
    }

    //--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
    //获取我的行程列表
    public function driverGetMyList()
    {
        $user = $this->_user;
        $userId = $user['user_id'];

        $this->load->model('service/TripDriverService');

        $trips = $this->TripDriverService->getMyTripList($userId);

        $currentDate = date('Y-m-d');
        $resTrips = array();
        if (!empty($trips)) {
            foreach ($trips as $trip) {
                if (isset($trip['begin_date']) && $currentDate > $trip['begin_date']) {
                    $trip['is_expired'] = true;
                } else {
                    $trip['is_expired'] = false;
                }
                $resTrips[] = $trip;
            }
        }

        $this->_returnSuccess($this->_sortTripsByCreatedTime($resTrips));
    }

    public function passengerGetMyList()
    {
        $user = $this->_user;
        $userId = $user['user_id'];

        $this->load->model('service/TripPassengerService');

        $trips = $this->TripPassengerService->getMyTripList($userId);

        $currentDate = date('Y-m-d');
        $resTrips = array();
        if (!empty($trips)) {
            foreach ($trips as $trip) {
                if (isset($trip['begin_date']) && $currentDate > $trip['begin_date']) {
                    $trip['is_expired'] = true;
                } else {
                    $trip['is_expired'] = false;
                }
                $resTrips[] = $trip;
            }
        }

        $this->_returnSuccess($this->_sortTripsByCreatedTime($resTrips));
    }

    private function _sortTripsByCreatedTime($trips)
    {
        if (empty($trips) || !is_array($trips)) {
            return array();
        }

        $sortKeys = array();

        foreach ($trips as $trip) {
            $sortKeys[] = $trip['created_time'];
        }

        array_multisort($sortKeys, SORT_DESC, SORT_REGULAR, $trips);
        return $trips;
    }

    //--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
    //获取我的行程模板，包括人找车和车找人
    public function getTemplateList()
    {
        $user = $this->_user;
        $userId = $user['user_id'];

        $this->load->model('service/TripDriverService');
        $this->load->model('service/TripPassengerService');

        $driverTemplates = $this->TripDriverService->getMyTemplateList($userId);
        $passengerTemplates = $this->TripPassengerService->getMyTemplateList($userId);

        $this->_returnSuccess($this->_sortTripsByCreatedTime(array_merge($driverTemplates, $passengerTemplates)));;
    }

    //--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
    //删除模板，通过is_del物理删除
    public function deleteTemplate()
    {
        $input = $this->input->post();
        $user = $this->_user;
        $userId = $user['user_id'];
        $tripId = $input['trip_id'];
        $tripType = $input['trip_type'];


        if ($tripType == Config::TRIP_TYPE_DRIVER) {
            $this->load->model('service/TripDriverService');
            $trip = $this->TripDriverService->getTripByTripId($userId, $tripId);
            if ($trip['status'] != Config::TRIP_STATUS_DRAFT) {
                throw new StatusException(Status::$message[Status::TRIP_IS_NOT_TEMPLATE], Status::TRIP_IS_NOT_TEMPLATE);
            }
            $ret = $this->TripDriverService->deleteTrip($userId, $tripId);
        }
        if ($tripType == Config::TRIP_TYPE_PASSENGER) {
            $this->load->model('service/TripPassengerService');
            $trip = $this->TripPassengerService->getTripByTripId($userId, $tripId);
            if ($trip['status'] != Config::TRIP_STATUS_DRAFT) {
                throw new StatusException(Status::$message[Status::TRIP_IS_NOT_TEMPLATE], Status::TRIP_IS_NOT_TEMPLATE);
            }
            $ret = $this->TripPassengerService->deleteTrip($userId, $tripId);
        }

        $this->_returnSuccess($ret);
    }
    //--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------


}
