<?php

class GroupTripService extends CI_Model
{


    public function __construct()
    {
        parent::__construct();

    }

    //获取当前date之后的，status为正常的行程tripid
    public function getCurrentTripIdsByGroupIdAndTripType($groupId, $tripType)
    {
        $currentDate = date('Y-m-d');
        $this->load->model('dao/GroupTripDao');

        $groupTrips = $this->GroupTripDao->getListByGroupIdAndDateAndStatus($groupId, $currentDate, $tripType, Config::TRIP_STATUS_NORMAL);

        $ret = array();

        //从group的trip extend info中解压出行程快照，快照在发布和更新行程的时候写入
        if (is_array($groupTrips) && count($groupTrips) > 0) {
            foreach ($groupTrips as $groupTrip) {
                $trip = json_decode($groupTrip['extend_json_info']);
                $trip['top_time'] = $groupTrip['top_time'];
                $ret[] = $trip;
            }
        }

        return $ret;
    }


    public function publishTripsToGroup($tripId, $groupIds, $newTrip, $trip_type)
    {
        $this->load->model('dao/GroupTripDao');

        $groupTrips = array();
        foreach ($groupIds as $groupId) {
            $groupTrip = array();
            $groupTrip['trip_id'] = $tripId;
            $groupTrip['group_id'] = $groupId;
            $groupTrip['top_time'] = null;
            $groupTrip['trip_begin_date'] = $newTrip['begin_date'];
            $groupTrip['trip_type'] = $trip_type;
            $groupTrip['status'] = Config::GROUP_TRIP_STATUS_DEFAULT;
            $groupTrip['extend_json_info'] = json_encode($newTrip);

            $groupTrips[] = $groupTrip;

        }

        $this->GroupTripDao->insertMulti($groupTrips);
    }


}
