<?php
class Event
{
    public $start;
    public $end;

    public function __construct(DateTimeInterface $start, DateTimeInterface $end)
    {
        $this->start = $start;
        $this->end = $end;
    }
}

class Room
{
    public $id;
    public $shortName;
    public $name;

    private $unavailables;

    public function __construct($json)
    {
        $this->id = $json['id'];
        $this->shortName = $json['kurzname'];
        $this->name = $json['name'];

        $this->unavailables = [];
    }

    public function __toString()
    {
        return $this->shortName;
    }

    public function setUnavailable(Event $event)
    {
        $this->unavailables[] = $event; // test
    }
}

class TimeVector
{
    private $timeArray;
    public $start;

    public function __construct(array $timeArray, DateTimeInterface $start)
    {
        $this->timeArray = $timeArray;
        $this->start = $start;
    }

    public function get(DateTimeInterface $indexTime) : array
    {
        if ($indexTime < $start)
            throw new Exception("invalid time index");

        $seconds = $indexTime->getTimestamp() - $start->getTimestamp();
        $minutes = $seconds / 60;
        $i = $minutes / 15; // array contains rooms in 15 minute steps

        return $this->timeArray($i);
    }
}

class Importer 
{
    private $json;
    private $roomsOccupied;

    private function __construct(array &$json, array &$roomsOccupied)
    {
        $this->json = &$json;
        $this->roomsOccupied = &$roomsOccupied;
    }

    // private function removeEmpty($json) : array
    // {
    //     if (!isset($json)) return []; // temporary
    //     $cleanJson = [];
    //     foreach ($this->json as $element) {
    //         if (count($element) > 0) $cleanJson[] = $element;
    //     }
    //     return $cleanJson;
    // }

    private function getDays(string $week) : array
    {
        $days = $this->json['stundenplan']['kalenderwochen'][$week]['wochentage']; // exception handling
        // return $this->removeEmpty($days);
        return $days;
    }

    private function getEventInfos(array $day)
    {
        // return $this->removeEmpty($day['termine']); // exception handling
        if (count($day) == 0) return [];
        return $day['termine'];
    }

    // private function getRoom(string $roomId) : Room
    // {
    //     if (!array_key_exists($roomId, $this->roomsOccupied)) {
    //         $roomJson = $this->json['veranstaltungsorte'][$roomId]; // exception handling
    //         if (count($roomJson) == 0) throw new Exception("room json empty"); // temporary
    //         $this->roomsOccupied[$roomId] = new Room($roomJson);
    //     }
    //     return $this->roomsOccupied[$roomId];
    // }

    private function getRooms() : array
    {
        $rooms = [];
        // foreach ($this->json['veranstaltungsorte'] as $roomJson) {
        //     $rooms[$roomJson['id']] = new Room($roomJson);
        // }
        return $this->json['veranstaltungsorte'];
    }

    private function makeRoomTimes($start, $end, $rooms) : array
    {
        $roomTimes = [];
        while ($start <= $end) {
            $roomTimes[] = $rooms;
            $start->add(new DateInterval('PT15M'));
        }
        return $roomTimes;
    }

    private function makeEvent(string $eventId) : Event
    {
        $eventJson = $this->json['termine'][$eventId]; // exception handling
        if (count($eventJson) == 0) throw new Exception("event json empty"); // temporary
        
        $eventDate = $eventJson['datum'];
        $eventStart = $eventJson['beginn'];
        $eventEnd = $eventJson['ende'];

        $format = 'Y-m-d H:i:s';
        $eventStartDateTime = DateTime::createFromFormat($format, $eventDate . ' ' . $eventStart);
        $eventEndDateTime = DateTime::createFromFormat($format, $eventDate . ' ' . $eventEnd);

        return new Event($eventStartDateTime, $eventEndDateTime);
    }

    public static function query(DateTimeInterface $start, DateTimeInterface $end) : array
    {
        $response = file_get_contents('response.json');
        $json = json_decode($response, true);

        $array = [];
        $importer = new Importer($json, $array); // refactor

        $rooms = $importer->getRooms();
        $roomTimes = $importer->makeRoomTimes($start, $end, $rooms);

        while ($start <= $end) {
            $week = $start->format('Y') . '-W' . $start->format('W');

            foreach ($importer->getDays($week) as $day) {
                foreach ($importer->getEventInfos($day) as $eventInfo) {
                    $roomId = $eventInfo['veranstaltungsort'];

                    $eventId = $eventInfo['id'];
                    $event = $importer->makeEvent($eventId);

                    $eventStart = $event->start;
                    while ($eventStart <= $event->end) {
                        unset($roomTimes[$eventStart][$roomId]);
                        $eventStart->add(new DateInterval('PT15M'));
                    }
                }
            }
            $start->add(new DateInterval('P7D'));
        }
        return $roomTimes;

        // while ($start <= $end) {
        //     $week = $start->format('Y') . '-W' . $start->format('W');

        //     foreach ($importer->getDays($week) as $day) {
        //         foreach ($importer->getEventInfos($day) as $eventInfo) {
        //             try {
        //                 $roomId = $eventInfo['veranstaltungsort'];
        //                 $room = $importer->getRoom($roomId);
    
        //                 $eventId = $eventInfo['id'];
        //                 $event = $importer->makeEvent($eventId);
    
        //                 $room->setUnavailable($event);
        //             } catch (Exception $e) {
        //                 // echo $e->getMessage();
        //             }
        //         }
        //     }
        //     $start->add(new DateInterval('P7D'));
        // }
        // return $roomsOccupied;
    }
}

// test
$start = new DateTime();
$start->setTimeStamp(1442786400 + 604800);
$end = new DateTime();
$end->setTimeStamp(1442786400 + 2 * 604800);

$rooms = Importer::query($start, $end);
print_r($rooms);
