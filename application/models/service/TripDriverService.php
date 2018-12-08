<?php

class TripDriverService extends CI_Model
{


    public function __construct()
    {
        parent::__construct();
    }

    public function getTripByTripId($userId, $tripId)
    {
        $this->load->model('dao/TripDriverDao');
        $trip = $this->TripDriverDao->getOneByTripId($userId, $tripId);
        if (empty($trip)) {
            throw new StatusException(Status::$message[Status::TRIP_NOT_EXIST], Status::TRIP_NOT_EXIST);
        }

        return $trip;
    }

    public function updateTrip($tripId, $userId, $tripDriverDetail)
    {
        if ($tripId == null || userId == null) {
            throw new StatusException(Status::$message[Status::TRIP_NOT_EXIST], Status::TRIP_NOT_EXIST);
        }

        $this->load->model('dao/TripDriverDao');
        $trip = $this->TripDriverDao->getOneByTripId($userId, $tripId);

        if($trip['status'] != Config::TRIP_STATUS_NORMAL){
            throw new StatusException(Status::$message[Status::TRIP_NOT_EXIST], Status::TRIP_NOT_EXIST);
        }

        //只有正常状态的行程才允许编辑
        $this->TripDriverDao->updateByTripIdAndStatus($userId, $tripId, Config::TRIP_STATUS_NORMAL, $tripDriverDetail);

        return true;
    }


    public function saveTripTemplate($tripId, $userId, $tripDriverDetail)
    {
        $trip = array();
        $trip['trip_id'] = $tripId;
        $trip['user_id'] = $userId;
        $trip = array_merge($trip, $tripDriverDetail);

        $trip['status'] = Config::TRIP_STATUS_DRAFT;

        $this->load->model('dao/TripDriverDao');
        if ($tripId == null) {
            //创建新模板
            $this->load->model('redis/IdGenRedis');
            $trip['trip_id'] = $this->IdGenRedis->gen(Config::ID_GEN_KEY_TRIP);
            $this->TripDriverDao->insertOne($userId, $trip);
        } else {
            //更新旧模板
            $rows = $this->TripDriverDao->updateByTripIdAndStatus($userId, $tripId, Config::TRIP_STATUS_DRAFT, $trip);
            if ($rows == 0) {
                throw new StatusException(Status::$message[Status::TRIP_IS_NOT_TEMPLATE], Status::TRIP_IS_NOT_TEMPLATE);
            }
        }

        return true;
    }

    public function createNewTrip($userId, $tripDriverDetail, $user)
    {
        $this->load->model('dao/TripDriverDao');
        $this->load->model('redis/IdGenRedis');

        $trip = array();
        $trip['user_id'] = $userId;
        $trip['trip_id'] = $this->IdGenRedis->gen(Config::ID_GEN_KEY_TRIP);
        $trip = array_merge($trip, $tripDriverDetail);
        $trip['status'] = Config::TRIP_STATUS_NORMAL;

        //插入用户信息快照
        $trip['user_info'] = json_encode(
            array(
                "user_id" => $user["user_id"],
                "phone" => $user["phone"],
                "nick_name" => $user["nick_name"],
                "gender" => $user["gender"],
                "city" => $user["city"],
                "province" => $user["province"],
                "country" => $user["country"],
                "avatar_url" => $user["avatar_url"],
                "car_plate" => $user["car_plate"],
                "car_brand" => $user["car_brand"],
                "car_model" => $user["car_model"],
                "car_color" => $user["car_color"],
                "car_type" => $user["car_type"],
            )
        );

        $newTrip = $this->TripDriverDao->insertOne($userId, $trip);

        return $newTrip;
    }


    public function deleteTrip($userId, $tripId)
    {
        $this->load->model('dao/TripDriverDao');
        $ret = $this->TripDriverDao->deleteOne($userId, $tripId);

        return $ret;
    }

    public function getMyTripList($userId)
    {
        $this->load->model('dao/TripDriverDao');
        $trips = $this->TripDriverDao->getListByUserIdAndStatusArr($userId, array(Config::TRIP_STATUS_NORMAL, Config::TRIP_STATUS_CANCEL));

        return $trips;
    }

    public function getMyTemplateList($userId)
    {
        $this->load->model('dao/TripDriverDao');
        $trips = $this->TripDriverDao->getListByUserIdAndStatusArr($userId, array(Config::TRIP_STATUS_DRAFT));
        $tripsWithType = array();
        if (!empty($trips)) {
            foreach ($trips as $trip) {
                $tripWithType = $trip;
                $tripWithType['trip_type'] = Config::TRIP_TYPE_DRIVER;
            }
        }

        return $tripsWithType;
    }


}
