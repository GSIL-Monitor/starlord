<?php

defined('BASEPATH') OR exit('No direct script access allowed');


class TripDriverDetail
{
    public $userInfo;
    public $beginDate;
    public $beginTime;
    public $startLocationName;
    public $startLocationAddress;
    public $startLocationPoint;
    public $endLocationName;
    public $endLocationAddress;
    public $endLocationPoint;
    public $route;
    public $priceEveryone;
    public $priceTotal;
    public $seatNum;
    public $driverNoSmoke;
    public $driverLastMile;
    public $driverGoods;
    public $driverNeedDrive;
    public $driverChat;
    public $driverHighway;
    public $driverPet;
    public $driverCooler;
    public $tips;

    public function __construct($input)
    {
        $this->userInfo = $input["user_info"];
        $this->beginDate = $input["beginDate"];
        $this->beginTime = $input["beginTime"];
        $this->startLocationName = $input["startLocationName"];
        $this->startLocationAddress = $input["startLocationAddress"];
        $this->startLocationPoint = $input["startLocationPoint"];
        $this->endLocationName = $input["endLocationName"];
        $this->endLocationAddress = $input["endLocationAddress"];
        $this->endLocationPoint = $input["endLocationPoint"];
        $this->route = $input["route"];
        $this->priceEveryone = $input["priceEveryone"];
        $this->priceTotal = $input["priceTotal"];
        $this->seatNum = $input["seatNum"];
        $this->driverNoSmoke = $input["driverNoSmoke"];
        $this->driverLastMile = $input["driverLastMile"];
        $this->driverGoods = $input["driverGoods"];
        $this->driverNeedDrive = $input["driverNeedDrive"];
        $this->driverChat = $input["driverChat"];
        $this->driverHighway = $input["driverHighway"];
        $this->driverPet = $input["driverPet"];
        $this->driverCooler = $input["driverCooler"];
        $this->tips = $input["tips"];
    }
}


