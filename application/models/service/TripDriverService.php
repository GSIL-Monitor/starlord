<?php

class TripDriverService extends CI_Model
{


    public function __construct()
    {
        parent::__construct();
    }

    public function getAllTripsCount()
    {
        $this->load->model('dao/TripDriverDao');
        $count = $this->TripDriverDao->getCountOfAll();

        return $count['total'];
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

        if ($trip['status'] != Config::TRIP_STATUS_NORMAL) {
            throw new StatusException(Status::$message[Status::TRIP_NOT_EXIST], Status::TRIP_NOT_EXIST);
        }

        $tripDriverDetail['share_img_url'] = $this->getDriverTripImageUrl($tripId, $tripDriverDetail['start_location_name'], $tripDriverDetail['end_location_name'], $tripDriverDetail['price_everyone'], $tripDriverDetail['price_total']);
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
        $trip['share_img_url'] = $this->getDriverTripImageUrl($trip['trip_id'], $trip['start_location_name'], $trip['end_location_name'], $trip['price_everyone'], $trip['price_total']);
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

    public function addGroupInfoToTrip($userId, $tripId, $trip, $group)
    {
        unset($group['id']);
        unset($group['status']);
        unset($group['is_del']);
        unset($group['created_time']);
        unset($group['modified_time']);

        $groupInfoJson = $trip['group_info'];
        if (empty($groupInfoJson)) {
            $groups = array();
            $groups[] = $group;
            $trip['group_info'] = json_encode($groups);
        } else {
            $groups = json_decode($groupInfoJson, true);
            $groups[] = $group;
            $trip['group_info'] = json_encode($groups);
        }

        //只有正常状态的行程才允许编辑
        $this->TripDriverDao->updateByTripIdAndStatus($userId, $tripId, Config::TRIP_STATUS_NORMAL, $trip);

        return;
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
        if (empty($trips)) {
            return array();
        }
        return $trips;
    }

    public function getMyTemplateList($userId)
    {
        $this->load->model('dao/TripDriverDao');
        $trips = $this->TripDriverDao->getListByUserIdAndStatusArr($userId, array(Config::TRIP_STATUS_DRAFT));
        $tripsWithType = array();
        if (!empty($trips)) {
            foreach ($trips as $trip) {
                $trip['trip_type'] = Config::TRIP_TYPE_DRIVER;
                if ($trip['begin_date'] == Config::EVERYDAY_DATE) {
                    $trip['is_everyday'] = 1;
                } else {
                    $trip['is_everyday'] = 0;
                }
                $tripsWithType[] = $trip;
            }
        }

        return $tripsWithType;
    }

    private function getDriverTripImageUrl($tripId, $startLocationName, $endLocationName, $priceEveryone, $priceTotal)
    {
        $this->load->model('api/OssApi');

        $source = '/home/chuanhui/starlord/application/imgs/testpng.png';
        $firstNew = "/home/chuanhui/starlord/res/" . $tripId . "1.png";
        $secondNew = "/home/chuanhui/starlord/res/" . $tripId . "2.png";
        $thirdNew = "/home/chuanhui/starlord/res/" . $tripId . "3.png";

        $firstLine = array(
            'wm_text' => $startLocationName,
            'wm_type' => 'text',
            'wm_font_path' => '/home/chuanhui/starlord/application/ttf/songti.ttf',
            'wm_font_size' => '150',
            'wm_font_color' => 'ADFF2F',
            'wm_vrt_alignment' => 'bottom',
            'wm_hor_alignment' => 'center',
            'wm_padding' => '20',
        );

        $secondLine = array(
            'wm_text' => $endLocationName,
            'wm_type' => 'text',
            'wm_x_transp' => 0,
            'wm_font_path' => '/home/chuanhui/starlord/application/ttf/songti.ttf',
            'wm_font_size' => '150',
            'wm_font_color' => 'ADFF2F',
            'wm_vrt_alignment' => 'center',
            'wm_hor_alignment' => 'center',
            'wm_padding' => '180',
        );

        $thirdLine = array(
            'wm_text' => $priceEveryone . "/人，包车" . $priceTotal,
            'wm_type' => 'text',
            'wm_x_transp' => 0,
            'wm_font_path' => '/home/chuanhui/starlord/application/ttf/songti.ttf',
            'wm_font_size' => '150',
            'wm_font_color' => 'ADFF2F',
            'wm_vrt_alignment' => 'top',
            'wm_hor_alignment' => 'center',
            'wm_padding' => '340',
        );

        $this->imgHandler($source, $firstNew, $firstLine, true);
        $this->imgHandler($firstNew, $secondNew, $secondLine, true);
        $this->imgHandler($secondNew, $thirdNew, $thirdLine, true);

        $this->OssApi->uploadImg('test/' . $tripId . '.png', $thirdNew);

        unlink($firstNew);
        unlink($secondNew);
        unlink($thirdNew);

        return $this->OssApi->getSignedUrlForGettingObject('test/' . $tripId . '.png');
    }

    public function imgHandler($source, $new, $config, $output2File)
    {
        $config['image_library'] = 'gd2';
        $config['source_image'] = $source;
        $config['new_image'] = $new;
        $config['output_2_file'] = $output2File;

        $this->load->library('image_lib', $config);
        $this->image_lib->initialize($config);
        $this->image_lib->watermark();
        $this->image_lib->clear();
    }

}
