<?php declare(strict_types=1);
namespace Import;

require_once(__DIR__ . '/Utility/Event.php');
require_once(__DIR__ . '/Utility/Room.php');
require_once(__DIR__ . '/Utility/TimeVector.php');
use Import\Utility\{Event, Room, TimeVector};

class Importer
{
    public $CONFIG_PATH;
    public $DEBUG_CONFIG_PATH;

    private $json;
    private $start;
    private $end;
    private $options;

    public function __construct(array $json, \DateTimeInterface $start = null, \DateTimeInterface $end = null, bool $debug = false)
    {
        $this->json = $json;
        $this->start = $start;
        $this->end = $end;

        $this->CONFIG_PATH = __DIR__ . '/config.json';
        $this->DEBUG_CONFIG_PATH = __DIR__ . '/debug_config.json';

        // get configuration options from config file
        $config = ($debug ? $this->DEBUG_CONFIG_PATH : $this->CONFIG_PATH);
        $options = json_decode(file_get_contents($config), $assoc = true)['Importer'];
        $this->options = $options;
    }

    public static function query(\DateTimeInterface $start, \DateTimeInterface $end, bool $debug = false) : TimeVector
    {
        // build query
        $url = $options['calendar_base'];
        $data = [
            'dvon' => $start->format('Y-m-d'),
            'dbis' => $end->format('Y-m-d'),
            'zvon' => $start->format('H:i:s'),
            'zbis' => $end->format('H:i:s')
        ];
        $query = ($debug ? __DIR__ . "/$url" : $url . "&" . http_build_query($data)); // no parameters are used for debugging

        // get response and decode as json
        $json = json_decode(file_get_contents($query), $assoc = true);

        $importer = new Importer($json, $start, $end, $debug);

        // initialize the time vector with all possible rooms for every index
        $rooms = $importer->getRooms();
        $times = new TimeVector($importer->start, $importer->end, new \DateInterval('PT15M'), $rooms);

        // get events and remove rooms at those times
        $weekCounter = clone $importer->start;
        while ($weekCounter < $importer->end) { // in case time span is more than one week
            $week = $start->format('Y') . '-W' . $start->format('W');

            foreach ($importer->getDays($week) as $day) {
                foreach ($importer->getEventInfos($day) as $eventInfo) {
                    $roomId = $eventInfo['veranstaltungsort'];

                    $eventId = $eventInfo['id'];
                    try {
                        $event = $importer->makeEvent($eventId);
                    } catch (\Exception $e) {
                        continue;
                    }

                    if ($event->end < $times->start || $event->start > $times->end) {
                        continue;
                    }
                    $eventTime = clone $event->start;
                    while ($eventTime < $event->end) {
                        try {
                            $times->remove($eventTime, $roomId);
                        } catch (\InvalidArgumentException $e) {
                            ;
                        }
                        $eventTime->add($times->offset);
                    }
                }
            }
            $weekCounter->add(new \DateInterval('P7D'));
        }
        return $times;
    }

    /**
     * get all available rooms that could be occupied by events
     * 
     * @return array of Room objects
     */
    private function getRooms() : array
    {
        // base url where room data is located
        $url = $this->options['rooms_base'];

        // try to retrieve room data from url and decode as json
        if ($string = file_get_contents($url)) { // returns true on success
            $json = json_decode($string, $assoc = true); // returns NULL if json cannot be decoded or data is deeper than recursion limit
            if (is_null($json)) throw new \Exception('String with room data could not be decoded as JSON.');
        } else {
            throw new \Exception("String with room data could not be acquired from $url.");
        }

        // make array of Room objects out of json
        $rooms = [];
        foreach ($json as $roomJson) {
            try {
                $roomID = $roomJson['id'];
            } catch (\OutOfBoundsException $e) {
                throw new \Exception('ID property of room could not be found in JSON.');
            }
            $rooms[$roomID] = new Room($roomJson); // exception handling in constructor
        }
        return $rooms;
    }

    private function getDays(string $week) : array
    {
        $days = $this->json['stundenplan']['kalenderwochen'][$week]['wochentage']; // exception handling
        if (!isset($days)) return []; // temporary
        return $days;
    }

    private function getEventInfos(array $day) : array
    {
        if (count($day) == 0) return []; // temporary
        return $day['termine']; // exception handling
    }

    private function makeEvent(string $eventId) : Event
    {
        $eventJson = $this->json['termine'][$eventId]; // exception handling
        if (count($eventJson) == 0) throw new \Exception("event json empty"); // temporary

        $eventDate = $eventJson['datum'];
        $eventStart = $eventJson['beginn'];
        $eventEnd = $eventJson['ende'];

        $format = 'Y-m-d H:i:s';
        $eventStartDateTime = \DateTime::createFromFormat($format, $eventDate . ' ' . $eventStart);
        $eventEndDateTime = \DateTime::createFromFormat($format, $eventDate . ' ' . $eventEnd);

        return new Event($eventStartDateTime, $eventEndDateTime);
    }
}
