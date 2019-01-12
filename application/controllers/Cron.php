<?php

defined('BASEPATH') OR exit('No direct script access allowed');


class Cron extends Base
{

    public function __construct()
    {
        parent::__construct();

    }


    public function updateAllGroupsWithMemberAndTripNum()
    {
        $input = $this->input->post();
        $user = $this->_user;
        $userId = $user['user_id'];

        $groupId = $input['group_id'];
        $this->load->model('service/GroupService');
        $groups = $this->GroupService->getByGroupIds(array($groupId));

        $group = $groups[0];

        if ($group['owner_user_id'] == $userId) {
            $group['is_owner'] = 1;
        } else {
            $group['is_owner'] = 0;
        }

        $this->_returnSuccess($group);
    }

}
