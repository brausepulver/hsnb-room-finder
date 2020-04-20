<?php declare(strict_types=1);
namespace Import;

require_once(__DIR__ . '/Utility/Event.php');
require_once(__DIR__ . '/Utility/Room.php');
require_once(__DIR__ . '/Utility/TimeVector.php');
use Import\Utility\{Event, Room, TimeVector};

class Importer
{
    public static $CONFIG_PATH = __DIR__ . '/config.json';
    public static $DEBUG_CONFIG_PATH = __DIR__ . '/debug_config.json';

    private $json;
    private $start;
    private $end;
    private $options;

    private function __construct(array $json, \DateTimeInterface $start, \DateTimeInterface $end, array $options)
    {
        $this->json = $json;
        $this->start = $start;
        $this->end = $end;
        $this->options = $options;
    }

    public static function query(\DateTimeInterface $start, \DateTimeInterface $end, bool $debug = false) : TimeVector
    {
        // get configuration options from config file
        $config = ($debug ? self::$DEBUG_CONFIG_PATH : self::$CONFIG_PATH);
        $options = json_decode(file_get_contents($config), $assoc = true)['Importer'];

        // build query
        $url = $options['calendar_base'];
        echo realpath($url);
        $data = [
            'dvon' => $start->format('Y-m-d'),
            'dbis' => $end->format('Y-m-d'),
            'zvon' => $start->format('H:i:s'),
            'zbis' => $end->format('H:i:s')
        ];
        $query = ($debug ? __DIR__ . "/$url" : $url . "&" . http_build_query($data)); // no parameters are used for debugging

        // get response and decode as json
        $json = json_decode(file_get_contents($query), $assoc = true);

        $importer = new Importer($json, $start, $end, $options);

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

    private function getRooms() : array
    {
        $url = $this->options['rooms_base'];
        $json = json_decode(file_get_contents($url), $assoc = true);

        $rooms = [];
        foreach ($json as $roomJson) {
            $rooms[$roomJson['id']] = new Room($roomJson);
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
