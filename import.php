<?php
class Importer 
{
    public static function query(DateTimeInterface $start, DateTimeInterface $end) : array
    {
        $response = file_get_contents('response.json');
        $json = json_decode($response, true);
        $parser = new Parser($json);

        $roomsOccupied = [];

        while ($start <= $end) {
            $week = $start->format('Y') . '-W' . $start->format('W');

            foreach ($parser->getDays($week) as $day) {
                foreach ($parser->getEvents($day) as $event) {
                    $roomId = $event['veranstaltungsort'];
                    self::addRoom($roomId, $roomsOccupied);

                    $eventJson = $json['termine'][$event['id']];
                    if (count($eventJson) == 0) continue; // temporary
                    $eventDate = $eventJson['datum'];
                    $eventStart = $eventJson['beginn'];
                    $eventEnd = $eventJson['ende'];

                    $eventStartDate = DateTime::createFromFormat($format, $eventDate . ' ' . $eventStart);
                    $eventEndDate = DateTime::createFromFormat($format, $eventDate . ' ' . $eventEnd);

                    $room->setUnavailable($eventStartDate, $eventEndDate);
                }
            }

            $start->add(new DateInterval('P7D'));
        }

        return $roomsOccupied;
    }

    private static function addRoom(string $roomId, array $roomsOccupied)
    {
        if (!array_key_exists($roomId, $roomsOccupied)) {
            $roomJson = $json['veranstaltungsorte'][$roomId]; // exception handling
            if (count($roomJson) == 0) return; // temporary
            $roomsOccupied[$roomId] = new Room($roomJson);
        }
        $room = $roomsOccupied[$roomId];
    }

    private static function makeDateTime(string $eventDate, string $eventTime)
    {
        $format = 'Y-m-d H:i:s';
        return DateTime::createFromFormat($format, $eventDate . ' ' . $eventStart);
    }

    public static function invertRooms($rooms) : array
    {

    }
}

class Event
{
    public function __construct(DateTimeInterface $start, DateTimeInterface $end)
    {
        
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

    public function setUnavailable(DateTimeInterface $start, DateTimeInterface $end)
    {
        $this->unavailables[] = $start; // test
    }
}

$start = new DateTime();
$start->setTimeStamp(1442786400 + 604800);
$end = new DateTime();
$end->setTimeStamp(1442786400 + 604800);

$rooms = DataImport::query($start, $end);
print_r($rooms);
