<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class BaseController extends CI_Controller {

    public function __construct()
    {
        parent::__construct();

    }

	protected function _returnSuccess($aData = array())
	{
        echo json_encode(array('errno' => 0, 'errmsg' => '', 'data' => (object)null));
        exit;
	}
}
