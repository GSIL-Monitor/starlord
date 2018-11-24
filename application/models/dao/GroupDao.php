<?php

class GroupDao extends CI_Model
{
    const TABLE_NUM = 1;

    protected $table = "";
    protected $fields = array(
        "id",
        "group_id",
        "wx_gid",
        "member_num",
        "trip_num",
        "owner_user_id",
        "owner_wx_id",
        "notice",
        "status",
        "is_del",
        "created_time",
        "modified_time",
    );

    protected $primaryKey = 'id';
    protected $db = null;
    protected $dbConfName = "default";
    protected $tablePrefix = "group_";
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
            return $this->tablePrefix . (string)($shardKey % self::TABLE_NUM);
        }
    }

    public function getById($id)
    {
        $this->table = $this->_getShardedTable($id);
        $this->db = $this->getConn($this->dbConfName);
        $sql = "select * from " . $this->table . "where id = ?";

        $query = $this->db->query($sql, array($id));

        return $query->result();
    }

    public function getAll()
    {
        $this->table = $this->_getShardedTable(0);
        $this->db = $this->getConn($this->dbConfName);
        $sql = "select * from " . $this->table;

        $query = $this->db->query($sql, array());

        return $query->result();
    }


    public function add($testArr)
    {
        $this->table = $this->_getShardedTable(0);
        $this->db = $this->getConn($this->dbConfName);

        $insertFields = $this->fields;
        array_shift($insertFields);
        $sql = "insert into " . $this->table . " (" . implode(",", $insertFields) . ") values(?, ?, ?)";
        $query = $this->db->query($sql, $testArr);

        return true;
    }
}
