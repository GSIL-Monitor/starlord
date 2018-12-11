<?php

defined('BASEPATH') OR exit('No direct script access allowed');


class Search extends Base
{

    public function __construct()
    {
        parent::__construct();

    }

    public function driver()
    {
        $target_start = "(39.533898169423,116.99423118029)";
        $target_end = "(39.658079674774,115.95184874113)";
        $this->load->model('service/TestService');

        $this->_returnSuccess($this->SearchService->search($target_start, $target_end, 100));

    }

    public function passenger()
    {

    }
}
