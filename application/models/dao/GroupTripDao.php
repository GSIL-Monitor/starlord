<?php

class GroupTripDao extends CI_Model
{
    const TABLE_NUM = 1;

    protected $table = "";
    protected $fields = array(
        "id",
        "trip_id",
        "group_id",
        "top_time",
        "trip_begin_date",
        "trip_type",
        "status",
        "extend_json_info",
        "is_del",
        "created_time",
        "modified_time",
    );

    protected $primaryKey = 'id';
    protected $db = null;
    protected $dbConfName = "default";
    protected $tablePrefix = "grouptrip_";
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

    public function getListByGroupIdAndDateAndStatus($groupId, $date, $tripType, $status)
    {
        $this->table = $this->_getShardedTable(0);
        $this->db = $this->getConn($this->dbConfName);
        $sql = "select * from " . $this->table . " where group_id = ? and trip_type = ? and status = ? and is_del = ? and trip_begin_date > ?";

        $query = $this->db->query($sql, array($groupId, $tripType, $status, Config::RECORD_EXISTS, $date));

        if (!$query) {
            throw new StatusException(Status::$message[Status::DAO_FETCH_FAIL], Status::DAO_FETCH_FAIL, var_export($this->db, true));
        }

        return $query->result_array();
    }

    public function insertMulti($groupTrips)
    {
        if (empty($groupTrips) || !is_array($groupTrips)) {
            throw new StatusException(Status::$message[Status::DAO_INSERT_NO_FILED], Status::DAO_INSERT_NO_FILED, var_export($this->db, true));
        }
        $this->table = $this->_getShardedTable(0);
        $this->db = $this->getConn($this->dbConfName);

        $currentTime = date("Y-M-d H:m:s", time());

        $insertFields = $this->fields;
        array_shift($insertFields);

        $bindParams = array();
        $insertValues = array();

        foreach ($groupTrips as $v) {
            $v['created_time'] = $currentTime;
            $v['modified_time'] = $currentTime;
            $v['is_del'] = Config::RECORD_EXISTS;
            foreach ($insertFields as $field) {
                $questionMarks[] = '?';
                $bindParams[] = $v[$field];
            }
            $insertValues[] = "(" . implode(",", $questionMarks) . ")";
        }

        $sql = "insert into " . $this->table . " (" . implode(",", $insertFields) . ") values(" . implode(",", $insertValues) . ")";
        $query = $this->db->query($sql, $bindParams);

        if (!$query) {
            throw new StatusException(Status::$message[Status::DAO_INSERT_FAIL], Status::DAO_INSERT_FAIL, var_export($this->db, true));
        }

        return true;
    }

    public function getOneByGroupIdAndTripId($groupId, $tripId)
    {
        $this->table = $this->_getShardedTable(0);
        $this->db = $this->getConn($this->dbConfName);
        $sql = "select * from " . $this->table . " where group_id = ? and trip_id = ? and is_del = ?";

        $query = $this->db->query($sql, array($groupId, $tripId, Config::RECORD_EXISTS));

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

    public function updateByTripIdAndStatus($groupId, $tripId, $groupTrip)
    {
        if (empty($groupId) || empty($tripId) || !is_array($groupTrip) || count($groupTrip) == 0) {
            throw new StatusException(Status::$message[Status::DAO_UPDATE_FAIL], Status::DAO_UPDATE_FAIL, var_export($this->db, true));
        }

        $currentTime = date("Y-M-d H:m:s", time());

        $groupTrip['modified_time'] = $currentTime;

        $this->table = $this->_getShardedTable(0);
        $this->db = $this->getConn($this->dbConfName);

        $updateFields = array();
        $bindParams = array();
        foreach ($groupTrip as $k => $v) {
            $updateFields[] = $k . " = " . "?";
            $bindParams[] = $v;
        }
        $bindParams[] = $groupId;
        $bindParams[] = $tripId;
        $bindParams[] = Config::RECORD_EXISTS;
        $sql = "update " . $this->table . " set  " . implode(",", $updateFields) . " where group_id = ? and trip_id = ? and is_del = ?";

        $query = $this->db->query($sql, $bindParams);
        if (!$query) {
            throw new StatusException(Status::$message[Status::DAO_UPDATE_FAIL], Status::DAO_UPDATE_FAIL, var_export($this->db, true));
        }

        return true;
    }
}
