<?php

defined('BASEPATH') OR exit('No direct script access allowed');


class ExampleController extends BaseController {

    public function __construct()
    {
        parent::__construct();

    }

    public function example() {
        $this->load->model('service/ExampleService');
        $this->_returnSuccess();
    }
}
