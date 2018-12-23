<?php

class GroupTripService extends CI_Model
{


    public function __construct()
    {
        parent::__construct();

    }

    //确认群内有行程
    public function ensureGroupHasTrip($groupId, $tripId)
    {
        $this->load->model('dao/GroupTripDao');

        $groupTrip = $this->GroupTripDao->getOneByGroupIdAndTripId($groupId, $tripId);
        if (empty($groupTrip)) {
            throw new StatusException(Status::$message[Status::GROUP_HAS_NO_TRIP], Status::GROUP_HAS_NO_TRIP);
        }

        return;
    }

    //获取当前date之后的，status为正常的行程tripid
    public function getCurrentTripIdsByGroupIdAndTripType($groupId, $tripType)
    {
        $currentDate = date('Y-m-d');
        $this->load->model('dao/GroupTripDao');

        $groupTrips = $this->GroupTripDao->getListByGroupIdAndDate($groupId, $currentDate, $tripType);

        $ret = array();

        //从group的trip extend info中解压出行程快照，快照在发布和更新行程的时候写入
        if (is_array($groupTrips) && count($groupTrips) > 0) {
            foreach ($groupTrips as $groupTrip) {
                $trip = json_decode($groupTrip['extend_json_info'], true);
                $trip['top_time'] = $groupTrip['top_time'];
                $ret[] = $trip;
            }
        }

        return $ret;
    }


    public function publishTripToGroup($tripId, $groupId, $trip, $trip_type)
    {
        $this->load->model('dao/GroupTripDao');

        $groupTrips = array();
        $groupTrip = array();
        $groupTrip['trip_id'] = $tripId;
        $groupTrip['group_id'] = $groupId;
        $groupTrip['top_time'] = null;
        $groupTrip['trip_begin_date'] = $trip['begin_date'];
        $groupTrip['trip_type'] = $trip_type;
        $groupTrip['status'] = Config::GROUP_TRIP_STATUS_DEFAULT;
        $groupTrip['extend_json_info'] = json_encode($trip);
        $groupTrips[] = $groupTrip;

        $this->GroupTripDao->insertMulti($groupTrips);
    }

    public function deleteTripsFromGroup($tripId)
    {
        $this->load->model('dao/GroupTripDao');
        return $this->GroupTripDao->deleteByTripId($tripId);
    }


    public function topOneTrip($groupId, $tripId)
    {
        $currentTime = date("Y-M-d H:i:s", time());

        $this->load->model('dao/GroupTripDao');

        $groupTrip = $this->GroupTripDao->getOneByGroupIdAndTripId($groupId, $tripId);
        if (empty($groupTrip)) {
            throw new StatusException(Status::$message[Status::GROUP_HAS_NO_TRIP], Status::GROUP_HAS_NO_TRIP);
        }

        $groupTrip['top_time'] = $currentTime;
        return $this->GroupTripDao->updateByTripId($groupId, $tripId, $groupTrip);
    }

    public function unTopOneTrip($groupId, $tripId)
    {
        $this->load->model('dao/GroupTripDao');

        $groupTrip = $this->GroupTripDao->getOneByGroupIdAndTripId($groupId, $tripId);
        if (empty($groupTrip)) {
            throw new StatusException(Status::$message[Status::GROUP_HAS_NO_TRIP], Status::GROUP_HAS_NO_TRIP);
        }
        $groupTrip['top_time'] = null;
        return $this->GroupTripDao->updateByTripId($groupId, $tripId, $groupTrip);
    }

}
