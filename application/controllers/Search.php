<?php

defined('BASEPATH') OR exit('No direct script access allowed');


class Test extends Base
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

    public function search()
    {
        $target_start = "(39.533898169423,116.99423118029)";
        $target_end = "(39.658079674774,115.95184874113)";
        $this->load->model('service/TestService');

        $this->_returnSuccess($this->TestService->search($target_start, $target_end, 100));

    }

    public function setkey()
    {
        $this->load->model('service/TestService');
        $input = $this->input->post();

        $this->_returnSuccess($this->TestService->setkey($input['a']));

    }

    public function getkey()
    {
        $this->load->model('service/TestService');

        $this->_returnSuccess($this->TestService->getkey());

    }

}
