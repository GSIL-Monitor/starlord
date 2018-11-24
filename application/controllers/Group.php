<?php

defined('BASEPATH') OR exit('No direct script access allowed');


class Group extends Base
{

    public function __construct()
    {
        parent::__construct();

    }

    public function getAll()
    {
        $this->load->model('service/TestService');
        $this->_returnSuccess($this->TestService->getAll());
    }


    public function add()
    {
        $this->load->model('service/TestService');
        $input = $this->input->post();
        for ($i = 1; $i < 100000; $i++) {
            $route = "测试路径" . $i;
            echo $route . "\n";
            $start_loc = "(" . (string)(39.0 + mt_rand() / mt_getrandmax() * 2) . "," . (string)(115.0 + mt_rand() / mt_getrandmax() * 2) . ")";
            $end_loc = "(" . (string)(39.0 + mt_rand() / mt_getrandmax() * 2) . "," . (string)(115.0 + mt_rand() / mt_getrandmax() * 2) . ")";
            $this->TestService->add($route, $start_loc, $end_loc);
        }


        $this->_returnSuccess(null);
    }


}
