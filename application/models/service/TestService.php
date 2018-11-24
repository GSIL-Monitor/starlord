<?php

class TestService extends CI_Model
{
    public function __construct()
    {
        parent::__construct();

    }

    public function getAll()
    {
        $this->load->model('dao/TestDao');

        return $this->TestDao->getAll();
    }

    public function add($route, $start_loc, $end_loc)
    {
        $this->load->model('dao/TestDao');

        $locArr[] = $route;
        $locArr[] = $start_loc;
        $locArr[] = $end_loc;

        return $this->TestDao->add($locArr);
    }

    public function search($target_start, $target_end, $count)
    {
        $this->load->model('dao/TestDao');
        return $this->TestDao->search($target_start, $target_end, $count);

    }


    public function setkey($v)
    {
        $this->load->model('redis/CacheRedis');
        $this->CacheRedis->setValue('aaa', $v);
    }

    public function getkey()
    {
        $this->load->model('redis/CacheRedis');
        return $this->CacheRedis->getValue('aaa');
    }
}
