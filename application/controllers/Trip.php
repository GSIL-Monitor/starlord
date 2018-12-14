<?php

defined('BASEPATH') OR exit('No direct script access allowed');


class Trip extends Base
{

    public function __construct()
    {
        parent::__construct();

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

        if ($trip['status'] != Config::TRIP_STATUS_NORMAL) {
            throw new StatusException(Status::$message[Status::TRIP_IS_NOT_NORMAL], Status::TRIP_IS_NOT_NORMAL);
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
        $ret = $this->TripDriverService->saveTripTemplate($tripId, $userId, $tripDriverDetail->getTripArray());

        $this->_returnSuccess($ret);
    }

    public function passengerSave()
    {
        $input = $this->input->post();
        $user = $this->_user;
        $userId = $user['user_id'];
        $tripId = $input['trip_id'];

        $tripPassengerDetail = new TripPassengerDetail($input);

        $this->load->model('service/TripPassengerService');
        $ret = $this->TripPassengerService->saveTripTemplate($tripId, $userId, $tripPassengerDetail->getTripArray());

        $this->_returnSuccess($ret);
    }

    //--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
    //发布行程
    public function driverPublish()
    {
        $input = $this->input->post();
        $user = $this->_user;
        $userId = $user['user_id'];
        $tripDriverDetail = new TripDriverDetail($input);

        $this->load->model('service/TripDriverService');

        if ($user['status'] == Config::USER_STATUS_FROZEN || $user['status'] == Config::USER_AUDIT_STATUS_FAIL) {
            throw new StatusException(Status::$message[Status::TRIP_HAS_NO_AUTH_TO_PUBLISH], Status::TRIP_HAS_NO_AUTH_TO_PUBLISH);
        }

        //发布到trip表
        $newTrip = $this->TripDriverService->createNewTrip($userId, $tripDriverDetail->getTripArray(), $this->_user);

        //获取用户所在群的id
        $this->load->model('service/GroupUserService');
        $groupIds = $this->GroupUserService->getGroupIdsByUserId($userId);
        if (!empty($groupIds)) {
            //同步到grouptrip表
            $this->load->model('service/GroupTripService');
            $this->load->model('service/GroupService');
            $this->GroupTripService->publishTripsToGroup($newTrip['trip_id'], $groupIds, $newTrip, Config::TRIP_TYPE_DRIVER);
            $this->GroupService->increaseTripInGroups($groupIds);
        }

        $this->_returnSuccess($newTrip);
    }

    public function passengerPublish()
    {
        $input = $this->input->post();
        $user = $this->_user;
        $userId = $user['user_id'];

        $tripPassengerDetail = new TripPassengerDetail($input);

        $this->load->model('service/TripPassengerService');

        if ($user['status'] == Config::USER_STATUS_FROZEN || $user['status'] == Config::USER_AUDIT_STATUS_FAIL) {
            throw new StatusException(Status::$message[Status::TRIP_HAS_NO_AUTH_TO_PUBLISH], Status::TRIP_HAS_NO_AUTH_TO_PUBLISH);
        }
        //发布到trip表
        $newTrip = $this->TripPassengerService->createNewTrip($userId, $tripPassengerDetail->getTripArray(), $this->_user);

        //获取用户所在群的id
        $this->load->model('service/GroupUserService');
        $groupIds = $this->GroupUserService->getGroupIdsByUserId($userId);
        if (!empty($groupIds)) {
            //同步到grouptrip表
            $this->load->model('service/GroupTripService');
            $this->load->model('service/GroupService');
            $this->GroupTripService->publishTripsToGroup($newTrip['trip_id'], $groupIds, $newTrip, Config::TRIP_TYPE_PASSENGER);
            $this->GroupService->increaseTripInGroups($groupIds);

        }

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
        $groupIds = $this->GroupUserService->getGroupIdsByUserId($userId);
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
        $groupIds = $this->GroupUserService->getGroupIdsByUserId($userId);
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
        if(!empty($trips)){
            foreach ($trips as $trip){
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
        if(!empty($trips)){
            foreach ($trips as $trip){
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
