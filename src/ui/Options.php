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

    /**
     * @param bool $default Ob es sich um den ersten Request einer Session handelt.
     *                      Ist das der Fall, werden Standardwerte zugewiesen,
     *                      anstatt auf $_GET zuzugreifen.
     */
    public function __construct(bool $default = false)
    {
        if ($default) {
            $this->setDefault();
            return;
        }

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

    private function setDefault()
    {
        $this->dayEnabled = true;
        $this->day = ''; // Aus footer.html implementieren.

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
        if ($this->dayEnabled && !empty($this->day)) {
            if ($this->timeframeEnabled && !empty($this->timeframeFrom) && !empty($this->timeframeTo)) {
                return new \DateTime($this->day . ' ' . $this->timeframeFrom);
            } else {
                return new \DateTime($this->day);
            }
        }
        return new \DateTime('today');
    }

    /**
     * @return \DateTime Ende des Anfragezeitraums.
     */
    public function getFinish() : \DateTime
    {
        if ($this->dayEnabled && !empty($this->day)) {
            if ($this->timeframeEnabled && !empty($this->timeframeFrom) && !empty($this->timeframeTo)) {
                return  new \DateTime($this->day . ' ' . $this->timeframeTo);
            } else {
                return (new \DateTime($this->day))->add(new \DateInterval('P1D'));
            }
        }
        return (new \DateTime('today'))->add(new \DateInterval('P1D'));
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