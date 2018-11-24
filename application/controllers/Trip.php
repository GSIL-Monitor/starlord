<?php

defined('BASEPATH') OR exit('No direct script access allowed');


class Test extends Base
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

        array_multisort($topTripsSortKeys, SORT_DESC, SORT_NUMERIC, $topTrips);
        array_multisort($restTripsSortKeys, SORT_DESC, SORT_NUMERIC, $restTrips);

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

        //获取当前date之后的，status为正常的trips
        $trips = $this->GroupTripService->getCurrentTripIdsByGroupIdAndTripType($groupId, $tripType);

        //格式化群内行程,按照createdtime排序，toptime置顶
        return $this->_sortTripsInGroup($trips);
    }

    //--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
    //获取行程详情
    public function driverGetDetailByTripId()
    {
        $this->_returnSuccess($this->_getDetailByTripId(Config::TRIP_TYPE_DRIVER));
    }

    public function passengerGetDetailByTripId()
    {
        $this->_returnSuccess($this->_getDetailByTripId(Config::TRIP_TYPE_PASSENGER));
    }

    private function _getDetailByTripId($tripType, $userId, $tripId)
    {
        $input = $this->input->post();
        $userId = $input['user_id'];
        $tripId = $input['trip_id'];
        if ($tripId == null) {
            throw new StatusException(Status::$message[Status::TRIP_NOT_EXIST], Status::TRIP_NOT_EXIST);
        }

        //无需鉴权，所有用户都能看行程详情，因为分享页需要
        if ($tripType == Config::TRIP_TYPE_DRIVER) {
            $this->load->model('service/TripDriverService');
            return $this->TripDriverService->getTripByTripId($userId, $tripId);

        } else {
            $this->load->model('service/TripPassengerService');
            return $this->TripPassengerService->getTripByTripId($userId, $tripId);
        }
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
        $ret = $this->TripDriverService->saveTripTemplate($tripId, $userId, $tripDriverDetail);

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
        $ret = $this->TripPassengerService->saveTripTemplate($tripId, $userId, $tripPassengerDetail);

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

        if($user['status'] == Config::USER_STATUS_FROZEN || $user['status'] == Config::USER_AUDIT_STATUS_FAIL){
            throw new StatusException(Status::$message[Status::TRIP_HAS_NO_AUTH_TO_PUBLISH], Status::TRIP_HAS_NO_AUTH_TO_PUBLISH);
        }

        //发布到trip表
        $newTrip = $this->TripDriverService->createNewTrip($userId, $tripDriverDetail, $this->_user);

        //获取用户所在群的id
        $this->load->model('service/GroupUserService');
        $groupIds = $this->GroupUserService->getGroupIdsByUserId($userId);
        if (!empty($groupIds)) {
            //同步到grouptrip表
            $this->load->model('service/GroupTripService');
            $this->GroupTripService->publishTripsToGroup($newTrip['trip_id'], $groupIds, $newTrip, Config::TRIP_TYPE_DRIVER);
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

        if($user['status'] == Config::USER_STATUS_FROZEN || $user['status'] == Config::USER_AUDIT_STATUS_FAIL){
            throw new StatusException(Status::$message[Status::TRIP_HAS_NO_AUTH_TO_PUBLISH], Status::TRIP_HAS_NO_AUTH_TO_PUBLISH);
        }
        //发布到trip表
        $newTrip = $this->TripPassengerService->createNewTrip($userId, $tripPassengerDetail, $this->_user);

        //获取用户所在群的id
        $this->load->model('service/GroupUserService');
        $groupIds = $this->GroupUserService->getGroupIdsByUserId($userId);
        if (!empty($groupIds)) {
            //同步到grouptrip表
            $this->load->model('service/GroupTripService');
            $this->GroupTripService->publishTripsToGroup($newTrip['trip_id'], $groupIds, $newTrip, Config::TRIP_TYPE_PASSENGER);
        }

        $this->_returnSuccess($newTrip);
    }

    //--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
    //更新我的行程
    public function driverUpdatMy()
    {
        $input = $this->input->post();
        $user = $this->_user;
        $userId = $user['user_id'];
        $tripId = $input['trip_id'];

        $tripDriverDetail = new TripDriverDetail($input);

        $this->load->model('service/TripDriverService');
        $ret = $this->TripDriverService->updateTrip($tripId, $userId, $tripDriverDetail);

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

        $ret = $this->TripPassengerService->updateTrip($tripId, $userId, $tripPassengerDetail);

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
        $ret = $this->TripDriverService->deleteTrip($userId, $tripId);

        $this->_returnSuccess($ret);
    }

    public function passengerDeleteMy()
    {
        $input = $this->input->post();
        $user = $this->_user;
        $userId = $user['user_id'];
        $tripId = $input['trip_id'];

        $this->load->model('service/TripPassengerService');
        $ret = $this->TripPassengerService->deleteTrip($userId, $tripId);

        $this->_returnSuccess($ret);
    }

    //--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
    //获取我的行程列表
    public function driverGetMyList()
    {
        $user = $this->_user;
        $userId = $user['user_id'];

        $this->load->model('service/TripDriverService');

        $trips = $this->TripDriverService->getMyTripList($userId);

        $this->_returnSuccess($this->_sortTripsByCreatedTime($trips));
    }


    public function passengerGetMyList()
    {
        $user = $this->_user;
        $userId = $user['user_id'];

        $this->load->model('service/TripPassengerService');

        $trips = $this->TripPassengerService->getMyTripList($userId);

        $this->_returnSuccess($this->_sortTripsByCreatedTime($trips));
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

        array_multisort($sortKeys, SORT_DESC, SORT_NUMERIC, $trips);
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

        $ret = null;
        if ($tripType == Config::TRIP_TYPE_DRIVER) {
            $ret = $this->TripDriverService->deleteTrip($userId, $tripId);
        }
        if ($tripType == Config::TRIP_TYPE_PASSENGER) {
            $ret = $this->TripPassengerService->deleteTrip($userId, $tripId);
        }

        $this->_returnSuccess($ret);
    }
    //--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
}
