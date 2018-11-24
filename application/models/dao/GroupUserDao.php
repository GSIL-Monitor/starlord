<?php

class GroupUserDao extends CI_Model
{
    const TABLE_NUM = 1;

    protected $table = "";
    protected $fields = array(
        "id",
        "user_id",
        "group_id",
        "status",
        "is_del",
        "created_time",
        "modified_time",
    );

    protected $primaryKey = 'id';
    protected $db = null;
    protected $dbConfName = "default";
    protected $tablePrefix = "groupuser_";
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
            if (self::TABLE_NUM == 1) {
                return $this->tablePrefix . "0";
            }
            return $this->tablePrefix . (string)($shardKey % self::TABLE_NUM);
        }
    }

    public function getOneByGroupIdAndUserId($userId, $groupId)
    {
        $this->table = $this->_getShardedTable($userId);
        $this->db = $this->getConn($this->dbConfName);
        $sql = "select * from " . $this->table . "where user_id = ? and group_id = ? and is_del = ?";

        $query = $this->db->query($sql, array($userId, $groupId, Config::RECORD_EXISTS));

        if (!$query) {
            throw new StatusException(Status::$message[Status::DAO_FETCH_FAIL], Status::DAO_FETCH_FAIL, var_export($this->db, true));
        } else if ($query->num_rows() == 0) {
            return array();
        } else if ($query->num_rows() == 1) {
            return $query->row_array();
        } else if ($query->num_rows() > 1) {
            throw new StatusException(Status::$message[Status::DAO_MORE_THAN_ONE_RECORD], Status::DAO_MORE_THAN_ONE_RECORD, var_export($this->db, true));
        }

        return $query->row_array();
    }

    public function getGroupsByUserId($userId)
    {
        $this->table = $this->_getShardedTable($userId);
        $this->db = $this->getConn($this->dbConfName);
        $sql = "select * from " . $this->table . "where user_id = ? and is_del = ?";

        $query = $this->db->query($sql, array($userId, Config::RECORD_EXISTS));

        if (!$query) {
            throw new StatusException(Status::$message[Status::DAO_FETCH_FAIL], Status::DAO_FETCH_FAIL, var_export($this->db, true));
        }

        return $query->result_array();
    }

}
