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
        $this->unavailables[] = $event->start; // test
    }
}

class Importer 
{
    private static function addRoom(string $roomId, array $roomsOccupied, array $json)
    {
        if (!array_key_exists($roomId, $roomsOccupied)) {
            $roomJson = $json['veranstaltungsorte'][$roomId]; // exception handling
            if (count($roomJson) == 0) return; // temporary
            $roomsOccupied[$roomId] = new Room($roomJson);
        }
        $room = $roomsOccupied[$roomId];
    }

    private static function makeEvent(string $eventId, array $json) : Event
    {
        $eventJson = $json['termine'][$eventId]; // exception handling
        if (count($eventJson) == 0) return NULL; // temporary
        
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
        $parser = new Parser($json);

        $roomsOccupied = [];

        while ($start <= $end) {
            $week = $start->format('Y') . '-W' . $start->format('W');

            foreach ($parser->getDays($week) as $day) {
                foreach ($parser->getEvents($day) as $eventInfo) {
                    $roomId = $eventInfo['veranstaltungsort'];
                    self::addRoom($roomId, $roomsOccupied, $json);

                    $eventId = $eventInfo['id'];
                    $event = self::makeEvent($eventId, $json);

                    $room = $roomsOccupied[$roomId];
                    $room->setUnavailable($event);
                }
            }
            $start->add(new DateInterval('P7D'));
        }
        return $roomsOccupied;
    }
}

class Parser
{
    private $json;

    public function __construct(array $json)
    {
        $this->json = $json;
    }

    function getDays(string $week) : array
    {
        // also handle exceptions
        $days = $json['stundenplan']['kalenderwochen'][$week]['wochentage'];
        return removeEmpty($days);
    }

    function getEvents(array $day)
    {
        return removeEmpty($day['termine']);
    }

    private function removeEmpty(array &$json) : array
    {
        $cleanJson = [];
        foreach ($json as $element) {
            if (count($element) > 0) $cleanJson[] = $element;
        }
        return $cleanJson;
    }
}

$start = new DateTime();
$start->setTimeStamp(1442786400 + 604800);
$end = new DateTime();
$end->setTimeStamp(1442786400 + 604800);

$rooms = DataImport::query($start, $end);
print_r($rooms);
