<?php

class ExampleDao extends CommonDao
{
    protected $sTable = null;
    protected $aFields = array(
        'id',
        'order_id',
        'driver_id',
        'canvas_id',
        'step_id',
        'order_info',
        'cycle_type',
        'date',
        'filter_status',
        'cheat_status',
        '_create_time',
        '_modify_time',
    );

    protected $iPrimaryKey = 'id';
    protected $sTablePrefix = "order_bucket_";
    protected $sDbConfName = "default";

    public function insertOne($aOrder)
    {
        $this->sTable = $this->_getShardedTable($aOrder['driver_id']);

        return $this->_insertOneRecord($aOrder);
    }

    public function getOneByOrderIdAndDriverIdAndCanvasId($iOrderId, $iDriverId, $iCanvasId){
        $this->sTable = $this->_getShardedTable($iDriverId);

        $aCondition = array(
            'driver_id' => $iDriverId,
            'canvas_id' => $iCanvasId,
            'order_id' => $iOrderId,
        );

        $aOrderList = $this->_getDataByCondition($aCondition, $this->aFields);
        if(count($aOrderList) == 1){
            return $aOrderList[0];
        }elseif (count($aOrderList) == 0){
            return array();
        }else{
            throw new StatusException(Status::$message[Status::DAO_MORE_THAN_ONE_RECORD], Status::DAO_MORE_THAN_ONE_RECORD, var_export($this->oCommonDb, true));
        }
    }

    public function getListByDriverIdAndCanvasId($iDriverId, $iCanvasId, $sDate = null){
        $this->sTable = $this->_getShardedTable($iDriverId);

        $aCondition = array(
            'driver_id' => $iDriverId,
            'canvas_id' => $iCanvasId,
            'cheat_status' => ORDER_IS_REGULAR,
            'filter_status' => ORDER_FILTERED_SUCCESS,
        );
        if (!empty($sDate)) {
            $aCondition['date'] = $sDate;
        }
        return $this->_getDataByCondition($aCondition, $this->aFields);
    }
}
