<?php

class CommonDao extends CI_Model
{
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

}