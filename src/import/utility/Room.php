<?php declare(strict_types=1);
namespace Import\Utility;

class Room
{
    public $id;
    public $id2;
    public $shortName;
    public $name;

    public $type;
    public $size;
    public $number;
    public $building;

    private $occupiedTimeFrames;

    public function __construct($json)
    {
        $this->id = $json['id'];
        $this->id2 = $json['id2'];
        $this->shortName = $json['kurzname'];
        $this->name = $json['name'];

        $this->building = substr($this->shortName, 2, 1);
        $this->number = substr($this->shortName, 4);

        $this->occupiedTimeFrames = [];
    }

    public function __toString()
    {
        return $this->shortName;
    }

    public function occupy(Event $event)
    {
        $this->occupiedTimeFrames[] = [$event->start, $event->end];
    }

    public function getAvailableTimeFrames(\DateTimeInterface $start, \DateTimeInterface $finish)
    {
        $interval = new \DateInterval('PT15M');
        $v = new TimeVector($start, $finish, $interval, true);

        /* Wenn keine Events für den Raum gespeichert wurden, ist occupiedTimeFrames leer.
           Ich gebe also die Start- und End-Zeit der Anfrage zurück. (z.B. 08:00 bis 20:00 Uhr) */
        if (count($this->occupiedTimeFrames) === 0) {
            return [ [clone $start, clone $finish] ];
        }
        /* Das Feld in der TimeVector-Variable $v wurde für jeden 15-Minuten-Index standardmäßig auf true gesetzt.
           Jetzt laufe ich alle registrierten Events ($timeFrames) durch, 
           und setze für jedes Event alle Indizes in $v auf falsch, die das Event zeitlich in dem Raum einnimmt. */
        foreach ($this->occupiedTimeFrames as $timeFrame) {
            for ($i = clone $timeFrame[0]; $i < $timeFrame[1]; $i->add($interval)) {
                $v->set($i, false);
            }
        }
        /* $available ist ein Feld der Form [bool], z.B. [true, false, true, false], 
           entsprechend der Information, ob der Raum um 8:00, 8:15, 8:30, 8:45 Uhr, etc. verfügbar ist. */
        $available = $v->toArray(); $availableOn = [];

        /* Aus dem [bool] $available wird jetzt ein Array der Form [ [DateTime $start, DateTime $ende], ... ] gemacht.
           Die Variable des Arrays ist $availableOn, und wird später (für die Verarbeitung in query.php) von der Funktion zurückgegeben.
           Dazu werden alle Zeit-Indizes in $available durchlaufen. */
        for ($i = 0, $j; $i < count($available); $i++) {
            // Überprüfen, ob $availableOn leer ist. D.h. ob es sich um das erste einzufügende Element handelt.
            $s = count($availableOn) > 0 ? count($availableOn[count($availableOn)-1]) : 0;

            // Wenn der Raum zu der aktuellen Zeit verfügbar ist, füge ein Array der Form [DateTime $start] an $availableOn an.
            if ($available[$i]) {
                if ($s === 2 || $s === 0) {
                    $availableOn[] = [$this->indexToTime($start, $i)];
                    // $j wird hochgezählt und später als DateTime $ende an das Array in der vorherigen Zeile angefügt.
                    $j = $this->indexToTime($start, $i);
                }
                $j->add($interval);
            }

            $s = count($availableOn) > 0 ? count($availableOn[count($availableOn)-1]) : 0;
            /* Überprüfen, ob der Raum nicht mehr verfügbar ist, aber es vorher war (es wurde ein [DateTime $start] angelegt),
               oder ob es sich bei dem Zeit-Index $i um die letzte Zeit in $available handelt (es muss ein DateTime $ende angefügt werden). */
            if ((!$available[$i] || $i === count($available)-1) && $s === 1) {
                $availableOn[count($availableOn)-1][1] = $j;
            }
        }
        return $availableOn;
    }

    private function indexToTime(\DateTimeInterface $start, int $index)
    {
        return (clone $start)->add(new \DateInterval('PT' . $index * 15 . 'M'));
    }

    /**
     * Vergleichen zweier Räume für die Sortierung des Arrays in query.php mittels usort.
     * 
     * @param Room $a
     * @param Room $b
     * 
     * @return int Ganzzahl, die < 0 wenn Raum $a kleiner als Raum $b, 
     *                           = 0 wenn Raum $a gleich Raum $b und
     *                           > 0 wenn Raum $a größer als Raum $b.
     */
    public static function compareRoom(Room $a, Room $b) : int
    {
        $number_a = $a->number;
        $number_b = $b->number;
        $building_a = $a->building;
        $building_b = $b->building;

        if ($building_a === $building_b) {
            return strcmp($number_a, $number_b);
        } else {
            return strcmp($building_a, $building_b);
        }
    }
}
