<?php

class UserService extends CI_Model
{


    public function __construct()
    {
        parent::__construct();

    }

    public function getUserByOpenId($openId)
    {
        $this->load->model('dao/UserDao');

        return $this->UserDao->getOneByOpenId($openId);
    }

    public function getUserByTicket($ticket)
    {
        $this->load->model('dao/UserDao');

        $user = $this->UserDao->getOneByTicket($ticket);

        return $user;
    }

    public function getUserByUserId($userId)
    {
        $this->load->model('dao/UserDao');

        $user = $this->UserDao->getOneByUserId($userId);

        return $user;
    }

    public function createNewUser($sessionKey, $openId, $ticket, $isValid)
    {
        $this->load->model('redis/IdGenRedis');
        $this->load->model('dao/UserDao');

        $user = array();
        $user['user_id'] = $this->IdGenRedis->gen(Config::ID_GEN_KEY_USER);
        $user['wx_session_key'] = $sessionKey;
        $user['wx_open_id'] = $openId;
        $user['ticket'] = $ticket;
        $user['is_valid'] = $isValid;
        $user['audit_status'] = Config::USER_AUDIT_STATUS_FAIL;
        $user['status'] = Config::USER_STATUS_OK;

        return $this->UserDao->insertOne($user);
    }

    public function updateSessionKeyAndTicketByUser($userId, $sessionKey, $ticket)
    {
        $this->load->model('dao/UserDao');

        $user = array();
        $user['wx_session_key'] = $sessionKey;
        $user['ticket'] = $ticket;

        return $this->UserDao->updateOneByUserId($userId, $user);
    }

    public function updateUser($user)
    {
        $this->load->model('dao/UserDao');
        if (empty($user) || empty($user['user_id'])) {
            throw new StatusException(Status::$message[Status::USER_NOT_EXIST], Status::USER_NOT_EXIST);
        }

        return $this->UserDao->updateOneByUserId($user['user_id'], $user);
    }
}
