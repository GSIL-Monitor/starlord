<?php

defined('BASEPATH') OR exit('No direct script access allowed');


class Common extends Base {

    public function __construct()
    {
        parent::__construct();

    }

    public function login() {
        $a = array("hello", "world");
        $this->_returnSuccess($a);
    }
}
