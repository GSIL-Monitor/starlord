<?php

class TripDao extends CI_Model
{
    const TABLE_NUM = 1;

    protected $table = "";
    protected $fields = array();

    protected $primaryKey = 'id';
    protected $db = null;
    protected $dbConfName = "default";
    protected $tablePrefix = "";
    protected static $dbResources = array();

    public function __construct()
    {
        parent::__construct();
    }

    public function getConn($dbConfName = null)
    {
        if ($dbConfName == null) {
            $dbConfName = $this->dbConfName;
        }
        if (!isset(self::$dbResources[$dbConfName])) {
            self::$dbResources[$dbConfName] = $this->load->database($dbConfName, true);
        }
        return self::$dbResources[$dbConfName];
    }

    public function reConn()
    {
        $this->db->reconnect();
    }

    protected function _getShardedTable($shardKey)
    {
        $this->db = $this->getConn($this->dbConfName);

        if (!isset($shardKey)) {
            throw new StatusException(Status::$message[Status::DAO_HAS_NO_SHARD_KEY], Status::DAO_HAS_NO_SHARD_KEY, var_export($this->oCommonDb, true));
        }
        if (ENVIRONMENT == 'development') {
            return $this->tablePrefix . '0';
        } else {
            //return $this->tablePrefix . (string)($shardKey % self::TABLE_NUM);
            return $this->tablePrefix . '0';
        }
    }

    public function getOneByTripId($userId, $tripId)
    {
        $this->table = $this->_getShardedTable($userId);
        $this->db = $this->getConn($this->dbConfName);
        $sql = "select * from " . $this->table . " where user_id = ? and trip_id = ? and is_del = ?";

        $query = $this->db->query($sql, array($userId, $tripId, Config::RECORD_EXISTS));

        if (!$query) {
            throw new StatusException(Status::$message[Status::DAO_FETCH_FAIL], Status::DAO_FETCH_FAIL, var_export($this->db, true));
        } else if ($query->num_rows() == 0) {
            return array();
        } else if ($query->num_rows() == 1) {
            return $query->row_array();
        } else if ($query->num_rows() > 1) {
            throw new StatusException(Status::$message[Status::DAO_MORE_THAN_ONE_RECORD], Status::DAO_MORE_THAN_ONE_RECORD, var_export($this->db, true));
        }
    }

    public function insertOne($userId, $trip)
    {
        $currentTime = date("Y-M-d H:i:s", time());

        $trip['created_time'] = $currentTime;
        $trip['modified_time'] = $currentTime;
        $trip['is_del'] = Config::RECORD_EXISTS;

        $this->table = $this->_getShardedTable($userId);
        $this->db = $this->getConn($this->dbConfName);

        if (empty($trip) || !is_array($trip) || count($trip) == 0) {
            throw new StatusException(Status::$message[Status::DAO_INSERT_NO_FILED], Status::DAO_INSERT_NO_FILED, var_export($this->db, true));
        }

        $questionMarks = array();
        $bindParams = array();
        foreach ($trip as $k => $v) {
            $insertFields[] = $k;
            $bindParams[] = $v;
            $questionMarks[] = '?';
        }
        $sql = "insert into " . $this->table . " (" . implode(",", $insertFields) . ") values(" . implode(",", $questionMarks) . ")";
        $query = $this->db->query($sql, $bindParams);

        if (!$query) {
            throw new StatusException(Status::$message[Status::DAO_INSERT_FAIL], Status::DAO_INSERT_FAIL, var_export($this->db, true));
        }

        return $trip;
    }

    public function updateByTripIdAndStatus($userId, $tripId, $status, $trip)
    {
        if (empty($userId) || empty($trip) || !is_array($trip) || count($trip) == 0) {
            throw new StatusException(Status::$message[Status::DAO_UPDATE_FAIL], Status::DAO_UPDATE_FAIL, var_export($this->db, true));
        }

        $currentTime = date("Y-M-d H:i:s", time());

        $trip['modified_time'] = $currentTime;

        $this->table = $this->_getShardedTable($userId);
        $this->db = $this->getConn($this->dbConfName);

        $updateFields = array();
        $bindParams = array();
        foreach ($trip as $k => $v) {
            $updateFields[] = $k . " = " . "?";
            $bindParams[] = $v;
        }
        $bindParams[] = $userId;
        $bindParams[] = $tripId;
        $bindParams[] = $status;
        $bindParams[] = Config::RECORD_EXISTS;
        $sql = "update " . $this->table . " set  " . implode(",", $updateFields) . " where user_id = ? and trip_id = ? and status = ? and is_del = ?";

        $query = $this->db->query($sql, $bindParams);
        if (!$query) {
            throw new StatusException(Status::$message[Status::DAO_UPDATE_FAIL], Status::DAO_UPDATE_FAIL, var_export($this->db, true));
        }

        return $this->db->affected_rows();
    }

    public function deleteOne($userId, $tripId)
    {
        if (empty($userId) || empty($tripId)) {
            throw new StatusException(Status::$message[Status::DAO_DELETE_FAIL], Status::DAO_DELETE_FAIL, var_export($this->db, true));
        }

        $currentTime = date("Y-M-d H:i:s", time());

        $trip['modified_time'] = $currentTime;

        $this->table = $this->_getShardedTable($userId);
        $this->db = $this->getConn($this->dbConfName);

        $bindParams[] = Config::RECORD_DELETED;
        $bindParams[] = $userId;
        $bindParams[] = $tripId;
        $sql = "update " . $this->table . " set  is_del = ? where user_id = ? and trip_id = ?";

        $query = $this->db->query($sql, $bindParams);
        if (!$query) {
            throw new StatusException(Status::$message[Status::DAO_DELETE_FAIL], Status::DAO_DELETE_FAIL, var_export($this->db, true));
        }

        return $this->db->affected_rows();
    }

    public function getListByUserIdAndStatusArr($userId, $statusArr)
    {
        if (empty($userId) || empty($statusArr) || !is_array($statusArr)) {
            throw new StatusException(Status::$message[Status::DAO_FETCH_FAIL], Status::DAO_FETCH_FAIL, var_export($this->db, true));
        }
        $this->table = $this->_getShardedTable($userId);
        $this->db = $this->getConn($this->dbConfName);

        $questionMarks = array();
        $bindParams = array();
        $bindParams[] = $userId;
        $bindParams[] = Config::RECORD_EXISTS;
        foreach ($statusArr as $k => $v) {
            $bindParams[] = $v;
            $questionMarks[] = '?';
        }

        $sql = "select * from " . $this->table . " where user_id = ? and is_del = ? and status in (" . implode(",", $questionMarks) . ")";

        $query = $this->db->query($sql, $bindParams);


        if (!$query) {
            throw new StatusException(Status::$message[Status::DAO_FETCH_FAIL], Status::DAO_FETCH_FAIL, var_export($this->db, true));
        }

        return $query->result_array();
    }

}
