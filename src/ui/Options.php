<?php declare(strict_types=1);
namespace UI;

class Options 
{
    public $dayEnabled;
    public $day;
    public $timeframeEnabled;
    public $timeframeFrom;
    public $timeframeTo;
    public $roomNumberEnabled;
    public $roomNumber;
    public $buildingNumberEnabled;
    public $buildingNumber;
    public $roomTypeEnabled;
    public $roomType;

    public function __construct()
    {
        $this->dayEnabled = isset($_GET['day_enabled']);
        $this->day = $_GET['day'];
    
        $this->timeframeEnabled = isset($_GET['timeframe_enabled']);
        $this->timeframeFrom = $_GET['timeframe_from'];
        $this->timeframeTo = $_GET['timeframe_to'];

        $this->roomNumberEnabled = isset($_GET['room_number_enabled']);
        $this->roomNumber = $_GET['room_number'];
    
        $this->buildingNumberEnabled = isset($_GET['building_number_enabled']);
        $this->buildingNumber = $_GET['building_number'];
    
        $this->roomTypeEnabled = isset($_GET['room_type_enabled']);
        $this->roomType = $_GET['room_type'];
    }

    public function getStart()
    {
        if ($this->dayEnabled && !empty($this->day)) {
            if ($this->timeframeEnabled && !empty($this->timeframeFrom) && !empty($this->timeframeTo)) {
                return new \DateTime($this->day . ' ' . $this->timeframeFrom);
            } else {
                return new \DateTime($this->day);
            }
        }
        return new \DateTime('today');
    }

    public function getFinish()
    {
        if ($this->dayEnabled && !empty($this->day)) {
            if ($this->timeframeEnabled && !empty($this->timeframeFrom) && !empty($this->timeframeTo)) {
                return  new \DateTime($this->day . ' ' . $this->timeframeFrom);
            } else {
                return (new \DateTime($this->day))->add(new \DateInterval('P1D'));
            }
        }
        return (new \DateTime('today'))->add(new \DateInterval('P1D'));
    }

    public function getConditions()
    {
        $conditions = [];
        if ($this->roomNumberEnabled) $conditions['room_number'] = $this->roomNumber;
        if ($this->buildingNumberEnabled) $conditions['building_number'] = $this->buildingNumber;
        if ($this->roomTypeEnabled) $conditions['room_type'] = $this->roomType;
        return $conditions;
    }
}