<?php declare(strict_types=1);
namespace Import;

require_once(__DIR__ . '/Utility/Event.php');
require_once(__DIR__ . '/Utility/Room.php');
require_once(__DIR__ . '/Utility/TimeVector.php');
use Import\Utility\{Event, Room, TimeVector};

class Importer
{
    /* Files from which configuration data will be loaded. A configuration file contains calendar_base and rooms_base URLs.
       $CONFIG_PATH configuration is for regular operation. 
       $DEBUG_CONFIG_PATH configuration is for debug operation. 
       It is used if the $debug variable in the constructor is set to true.
       It makes the object use test data located at tests/test_calendar_response.json and tests/test_room_response.json.
       */
    public static $CONFIG_PATH = __DIR__ . '/config.json';
    public static $DEBUG_CONFIG_PATH = __DIR__ . '/debug_config.json';

    private $json;
    private $start;
    private $end;
    private $options;

    /**
     * Construct an Importer object. 
     * Used to import room and room vacancy data.
     * 
     * @param \DateTimeInterface $start Time from which to start import.
     * @param \DateTimeInterace $end Time at which to end import.
     * @param bool $debug Wether to use test data or not.
     */
    public function __construct(\DateTimeInterface $start, \DateTimeInterface $end, bool $debug = false)
    {
        $this->start = $start;
        $this->end = $end;
        $this->debug = $debug;

        // get configuration options from config file
        $config = ($this->debug ? self::$DEBUG_CONFIG_PATH : self::$CONFIG_PATH);
        $this->options = json_decode(file_get_contents($config), $assoc = true)['Importer'];

        // build query
        $url = $this->options['calendar_base'];
        $data = [
            'dvon' => $start->format('Y-m-d'),
            'dbis' => $end->format('Y-m-d')
        ];
        $query = ($this->debug ? __DIR__ . "/$url" : $url . "&" . http_build_query($data)); // no parameters are used for debugging

        // get response and decode as json
        $this->json = json_decode(file_get_contents($query), $assoc = true);
    }

    public function getAvailableRooms() : array
    {
        $rooms = $this->getRooms();

        $weekCounter = clone $this->start;
        while ($weekCounter < $this->end) { // in case time span is more than one week
            $week = $this->start->format('Y') . '-W' . $this->start->format('W');

            $this->processWeek($week, $rooms);
            $weekCounter->add(new \DateInterval('P7D')); // increment by one week
        }
        return $rooms;
    }

    private function processWeek(string $week, array $rooms)
    {
        foreach ($this->getDays($week) as $day) {
            foreach ($this->getEventInfos($day) as $eventInfo) {
                // does the event have an associated room
                if (!isset($eventInfo['veranstaltungsort'])) { 
                    continue;
                } 
                $roomId = $eventInfo['veranstaltungsort'];

                $eventId = $eventInfo['id'];
                $eventJson = $this->json['termine'][$eventId];
                $event = new Event($eventJson);

                // does the event fall into the given time span
                if ($event->end < $this->start || $event->start > $this->end) { 
                    continue;
                }
                $rooms[$roomId]->occupy($event);
            }
        }
    }

    public function getFilteredRooms(array $conditions) : array
    {
        $rooms = $this->getAvailableRooms();
        $filteredRooms = [];

        foreach ($rooms as $room) {
            if ($this->checkRoom($room, $conditions)) $filteredRooms[] = $room;
        }
        return $filteredRooms;
    }

    private function checkRoom(Room $room, array $conditions) : bool
    {
        $roomType = '';
        if (isset($conditions['room_type'])) {
            $roomTypes = self::getRoomTypes();
            $roomType = $roomTypes[$conditions['room_type']];
        }
        if (isset($conditions['room_number'])) {
            if ($conditions['room_number'] !== $room->number) return false;
        }
        if (isset($conditions['building_number'])) {
            if ($conditions['building_number'] !== $room->building) return false;
        }
        if (isset($conditions['room_type'])) {
            if (strpos($room->name, $roomType) === false) return false;
        }
        return true;
    }

    /**
     * Get all possible rooms.
     * 
     * @return array of Room objects.
     */
    public function getRooms() : array
    {
        // base url where room data is located
        $url = $this->options['rooms_base'];

        // try to retrieve room data from url and decode as json
        $query = ($this->debug ? __DIR__ . "/$url" : $url);
        if ($string = file_get_contents($query)) { // returns true on success
            $json = json_decode($string, $assoc = true); // returns NULL if json cannot be decoded or data is deeper than recursion limit
            if (is_null($json)) throw new \Exception('String with room data could not be decoded as JSON.');
        } else {
            throw new \Exception("String with room data could not be acquired from $url.");
        }

        // make array of Room objects from json
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

    /**
     * Get the JSON for all days in a week.
     * 
     * @param string $week in the from of [Year]-W[Week-Number], e.g. 2020-W16.
     * @return array JSON for days in week.
     */
    private function getDays(string $week) : array
    {
        if (!empty($this->json['stundenplan']['kalenderwochen'])) {
            $days = $this->json['stundenplan']['kalenderwochen'][$week]['wochentage'];
            if (isset($days)) {
                return $days;
            }
        }
        return [];
    }

    /** 
     * Get the JSON for all event stubs in a day.
     * 
     * @param array $day JSON for day.
     * @return array JSON for event stubs for day.
     */
    private function getEventInfos(array $day) : array
    {
        if (count($day) == 0) return [];
        return $day['termine'];
    }

    /**
     * Get unique room types of all possible rooms.
     * 
     * @return array Room types as strings.
     */
    public static function getRoomTypes()
    {
        $roomTypes = [
            'Seminarraum',
            'EDV-Labor',
            'PC-Pool',
            'Hörsaal',
            'Beratung',
        ];
        return $roomTypes;
    }
}
