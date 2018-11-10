<?php

class ExampleService extends CI_Model
{
    public function __construct()
    {
        parent::__construct();

    }

    public function finishOrder($iOrderId, $iDriverId, $iCanvasId, $iStepId, $driverOrder){
        $this->load->model('dao/ExampleDao');

        $this->LockRedis->lock('lockkey');
        DbTansactionHanlder::begin('default');

        try{
            //do dao read and write

            DbTansactionHanlder::commit('default');
            $this->LockRedis->unLock('lockkey');
        }catch (Exception $e){
            DbTansactionHanlder::rollBack('default');
            $this->LockRedis->unLock('lockkey');
            throw $e;
        }
    }
}
