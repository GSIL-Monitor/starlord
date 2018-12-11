<?php

class TestService extends CI_Model
{
    public function __construct()
    {
        parent::__construct();

    }

    public function search($target_start, $target_end, $count)
    {
        $this->load->model('dao/TestDao');
        return $this->TestDao->search($target_start, $target_end, $count);

    }


}
