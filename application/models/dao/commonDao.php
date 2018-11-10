<?php

class CommonDao extends CI_Model
{
    const TABLE_NUM = 1000;

    protected $sTable = "";
    protected $aFields = array();
    protected $sPrimaryKey = "";
    protected $oCommonDb = null;
    protected $sDbConfName = "";
    protected $sTablePrefix = "";
    protected static $aDbResources = array();

    public function __construct()
    {
        parent::__construct();
    }

    public function getConn($sDbConfName = null)
    {
        if ($sDbConfName == null) {
            $sDbConfName = $this->sDbConfName;
        }
        if (!isset(self::$aDbResources[$sDbConfName])) {
            self::$aDbResources[$sDbConfName] = $this->load->database($sDbConfName, true);
        }
        return self::$aDbResources[$sDbConfName];
    }

    public function reConn()
    {
        $this->oCommonDb->reconnect();
    }

    protected function _getShardedTable($iDriverId)
    {
        $this->oCommonDb = $this->getConn($this->sDbConfName);

        if (!isset($iDriverId)) {
            throw new StatusException(Status::$message[Status::DAO_HAS_NO_SHARD_KEY], Status::DAO_HAS_NO_SHARD_KEY, var_export($this->oCommonDb, true));
        }
        if (ENVIRONMENT == 'development') {
            return $this->sTablePrefix . '0';
        } else {
            return $this->sTablePrefix . (string)($iDriverId % self::TABLE_NUM);
        }
    }

    protected function _getDataByCondition($aCondition, $aFields = null, $aGroup = array(), $aOrderBy = array(), $aLimit = array())
    {
        $fStartTime = microtime(true);

        $this->oCommonDb = $this->getConn($this->sDbConfName);

        if (isset($aFields)) {
            $aFields = "*";
        }

        $this->oCommonDb->select($aFields);
        $this->oCommonDb->from($this->sTable);

        if (is_array($aCondition) && count($aCondition) > 0) {
            foreach ($aCondition as $k => $v) {
                if (is_array($v)) {
                    $this->oCommonDb->where_in($k, $v);
                } else {
                    $this->oCommonDb->where($k, $v);
                }
            }
        }
        if ($aGroup) {
            foreach ($aGroup as $k => $v) {
                $this->oCommonDb->group_by($v);
            }
        }
        if ($aOrderBy) {
            foreach ($aOrderBy as $k => $v) {
                $this->oCommonDb->order_by($k, $v);
            }
        }

        if ((isset($aLimit['offset']) && $aLimit['offset']) || (isset($aLimit['limit']) && $aLimit['limit'])) {
            $iLimit = intval($aLimit['limit']);
            $iOffSet = intval($aLimit['offset']);
            $this->oCommonDb->limit($iLimit, $iOffSet);
        }

        $oQuery = $this->oCommonDb->get();

        $fEndTime = microtime(true);
        $fProcTime = ($fEndTime - $fStartTime) * 1000;
        $sQuery = $this->oCommonDb->last_query();
        $sHostName = $this->oCommonDb->hostname;
        $sDataBase = $this->oCommonDb->database;
        $iPort = $this->oCommonDb->port;

        if (!$oQuery) {
            com_mysql_failed(
                $sHostName,
                $iPort,
                $sDataBase,
                $sQuery,
                $fProcTime,
               Status::DAO_FETCH_FAIL,
                Status::$message[Status::DAO_FETCH_FAIL]
            );

            throw new StatusException(Status::$message[Status::DAO_FETCH_FAIL], Status::DAO_FETCH_FAIL, var_export($this->oCommonDb, true));
        } else {
            if ($oQuery->num_rows() > 0) {
                com_mysql_success(
                    $sHostName,
                    $iPort,
                    $sDataBase,
                    $sQuery,
                    $fProcTime,
                    $oQuery->result_array()
                );
                return $oQuery->result_array();
            } else {
                com_mysql_success(
                    $sHostName,
                    $iPort,
                    $sDataBase,
                    $sQuery,
                    $fProcTime,
                    array()
                );
                return array();
            }
        }
    }

    protected function _insertOneRecord($aFields)
    {
        $fStartTime = microtime(true);

        $this->oCommonDb = $this->getConn($this->sDbConfName);

        $query = $this->oCommonDb->insert($this->sTable, $aFields);

        $fEndTime = microtime(true);
        $fProcTime = ($fEndTime - $fStartTime) * 1000;
        $sQuery = $this->oCommonDb->last_query();
        $sHostName = $this->oCommonDb->hostname;
        $sDataBase = $this->oCommonDb->database;
        $iPort = $this->oCommonDb->port;

        if (!$query) {
            com_mysql_failed(
                $sHostName,
                $iPort,
                $sDataBase,
                $sQuery,
                $fProcTime,
                Status::DAO_INSERT_FAIL,
                Status::$message[Status::DAO_INSERT_FAIL]
            );
            throw new StatusException(Status::$message[Status::DAO_INSERT_FAIL], Status::DAO_INSERT_FAIL, var_export($this->oCommonDb, true));
        } else {
            com_mysql_success(
                $sHostName,
                $iPort,
                $sDataBase,
                $sQuery,
                $fProcTime,
                array()
            );
            //如果表中无auto_increment字段, insert_id则返回0
            return $this->oCommonDb->insert_id();
        }
    }

    protected function _updateByCondition($aCondition, $aFields)
    {
        $fStartTime = microtime(true);

        $this->oCommonDb = $this->getConn($this->sDbConfName);

        if (is_array($aCondition) && count($aCondition) > 0) {
            foreach ($aCondition as $k => $v) {
                if (is_array($v)) {
                    $this->oCommonDb->where_in($k, $v);
                } else {
                    $this->oCommonDb->where($k, $v);
                }
            }
        } else {
            throw new StatusException(Status::$message[Status::DAO_UPDATE_WITHOUT_CONDITION], Status::DAO_UPDATE_WITHOUT_CONDITION, var_export($this->oCommonDb, true));
        }
        $query = $this->oCommonDb->update($this->sTable, $aFields);

        $fEndTime = microtime(true);
        $fProcTime = ($fEndTime - $fStartTime) * 1000;
        $sQuery = $this->oCommonDb->last_query();
        $sHostName = $this->oCommonDb->hostname;
        $sDataBase = $this->oCommonDb->database;
        $iPort = $this->oCommonDb->port;

        if (!$query) {
            com_mysql_failed(
                $sHostName,
                $iPort,
                $sDataBase,
                $sQuery,
                $fProcTime,
                Status::DAO_UPDATE_FAIL,
                Status::$message[Status::DAO_UPDATE_FAIL]
            );
            throw new StatusException(Status::$message[Status::DAO_UPDATE_FAIL], Status::DAO_UPDATE_FAIL, var_export($this->oCommonDb, true));
        } else {
            com_mysql_success(
                $sHostName,
                $iPort,
                $sDataBase,
                $sQuery,
                $fProcTime,
                array()
            );
            return $this->oCommonDb->affected_rows();
        }
    }

}
