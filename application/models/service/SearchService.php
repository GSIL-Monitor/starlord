<?php

class SearchService extends CI_Model
{
    public function __construct()
    {
        parent::__construct();

    }

    public function search($tripType, $beginDate, $beginTime, $targetStart, $targetEnd)
    {
        $startAndEndRoundTrips = null;
        if ($tripType == Config::TRIP_TYPE_DRIVER) {
            $this->load->model('dao/TripDriverDao');
            $startAndEndRoundTrips = $this->TripDriverDao->search($beginDate, $beginTime, $targetStart, $targetEnd);
        } else {
            $this->load->model('dao/TripPassengerDao');
            $startAndEndRoundTrips = $this->TripPassengerDao->search($beginDate, $beginTime, $targetStart, $targetEnd);
        }



        if (empty($startAndEndRoundTrips)) {
            return array();
        }

        $resTrips = array();
        $sortKeys = array();

        foreach ($startAndEndRoundTrips as $trip) {
            $ratio = $trip['sum_distance'] / $trip['total_distance'];
            $score = (0.5 - $ratio) / 0.5 * 100;
            if ($score < 0) {
                $score = 0;
            }
            unset($trip['sum_distance']);
            unset($trip['total_distance']);
            $trip['score'] = $score;

            $sortKeys[] = $score;
            $resTrips[] = $trip;
        }

        array_multisort($sortKeys, SORT_DESC, SORT_NUMERIC, $resTrips);
        return $resTrips;
    }


}
