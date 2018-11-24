<?php

defined('BASEPATH') OR exit('No direct script access allowed');


class TripPassengerDetail
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
    public $priceEveryone;
    public $peopleNum;
    public $passengerNoSmoke;
    public $passengerLastMile;
    public $passengerGoods;
    public $passengerCanDrive;
    public $passengerChat;
    public $passengerLuggage;
    public $passengerPet;
    public $passengerNoCarsickness;
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
        $this->priceEveryone = $input["priceEveryone"];
        $this->peopleNum = $input["peopleNum"];
        $this->passengerNoSmoke = $input["passengerNoSmoke"];
        $this->passengerLastMile = $input["passengerLastMile"];
        $this->passengerGoods = $input["passengerGoods"];
        $this->passengerCanDrive = $input["passengerCanDrive"];
        $this->passengerChat = $input["passengerChat"];
        $this->passengerLuggage = $input["passengerLuggage"];
        $this->passengerPet = $input["passengerPet"];
        $this->passengerNoCarsickness = $input["passengerNoCarsickness"];
        $this->tips = $input["tips"];
    }
}