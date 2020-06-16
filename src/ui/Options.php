<?php declare(strict_types=1);
namespace UI;

/**
 * Diese Klasse dient dazu, durch form.php abgeschickte Optionen zu speichern,
 * und bei einem weiteren Request form.php mit den selben Optionen auszufüllen.
 * 
 * Die Lebensdauer beträgt dabei einen Request.
 * 
 * Im ersten Request einer Session dient die Klasse dazu, Standardwerte in form.php einzutragen.
 */
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

    public function populate() : void
    {
        $this->dayEnabled = isset($_GET['day_enabled']);
        $this->day = $this->dayEnabled ? $_GET['day'] : (new \DateTime('today'))->format('Y-m-d');
    
        $this->timeframeEnabled = isset($_GET['timeframe_enabled']);
        $this->timeframeFrom = $this->timeframeEnabled ? $_GET['timeframe_from'] : '08:00';
        $this->timeframeTo = $this->timeframeEnabled ? $_GET['timeframe_to'] : '20:00';

        $this->roomNumberEnabled = isset($_GET['room_number_enabled']);
        $this->roomNumber = $this->roomNumberEnabled ? $_GET['room_number'] : '';
    
        $this->buildingNumberEnabled = isset($_GET['building_number_enabled']);
        $this->buildingNumber = $this->buildingNumberEnabled ? $_GET['building_number'] : '1';
    
        $this->roomTypeEnabled = isset($_GET['room_type_enabled']);
        $this->roomType = $this->roomTypeEnabled ? $_GET['room_type'] : '0';
    }

    public function populateDefault() : void
    {
        $this->dayEnabled = true;
        $this->day = (new \DateTime('today'))->format('Y-m-d');

        $this->timeframeEnabled = true;
        $this->timeframeFrom = '08:00';
        $this->timeframeTo = '20:00';

        $this->roomNumberEnabled = false;
    
        $this->buildingNumberEnabled = false;
    
        $this->roomTypeEnabled = false;
    }

    /**
     * @return \DateTime Start des Anfragezeitraums.
     */
    public function getStart() : \DateTime
    {
        return new \DateTime("$this->day $this->timeframeFrom");
    }

    /**
     * @return \DateTime Ende des Anfragezeitraums.
     */
    public function getFinish() : \DateTime
    {
        return new \DateTime("$this->day $this->timeframeTo");
    }

    /**
     * @return array Bedingungen zur Filterung der Räume innerhalb der Anfrage.
     *               Diese zur Verarbeitung des Room-Arrays in Importer\getFilteredRooms genutzt.
     */
    public function getConditions() : array
    {
        $conditions = [];
        if ($this->roomNumberEnabled) $conditions['room_number'] = $this->roomNumber;
        if ($this->buildingNumberEnabled) $conditions['building_number'] = $this->buildingNumber;
        if ($this->roomTypeEnabled) $conditions['room_type'] = $this->roomType;
        return $conditions;
    }
}