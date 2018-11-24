<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Base extends CI_Controller
{
    protected $_user = null;

    public function __construct()
    {
        parent::__construct();

        $this->load->model('service/UserService');
        $input = $this->input->post();
        if (empty($input['ticket'])) {
            throw new StatusException(Status::$message[Status::USER_HAS_NO_TICKET], Status::USER_HAS_NO_TICKET);
        }
        $user = $this->UserService->getUserByTicket($input['ticket']);
        if (!empty($user) && is_array($user)) {
            if ($user['status'] == Config::USER_STATUS_FROZEN) {
                throw new StatusException(Status::$message[Status::USER_FROZEN], Status::USER_FROZEN);
            }
            $this->_user = $user;
        } else {
            throw new StatusException(Status::$message[Status::USER_TICKET_NOT_EXIST], Status::USER_TICKET_NOT_EXIST);
        }
    }

    protected function _returnSuccess($data = array())
    {
        echo json_encode(array('errno' => 0, 'errmsg' => '', 'data' => $data));
        exit;
    }
}
