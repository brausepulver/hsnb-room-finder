<?php
require_once('./import-utility.php');

class Importer 
{
    private $json;
    private $start;
    private $end;

    private function __construct(array $json, DateTimeInterface $start, DateTimeInterface $end)
    {
        $this->json = $json;
        $this->start = $start;
        $this->end = $end;
    }

    public static function query(DateTimeInterface $start, DateTimeInterface $end)
    {
        // build query
        $url = 'https://portal.hs-nb.de/ext/stundenplananzeige/index.php?modul=Termin&seite=plandaten';
        $data = [
            'dvon' => $start->format('Y-m-d'),
            'dbis' => $end->format('Y-m-d'),
            // these options return less rooms => do not use?
            // 'zvon' => $start->format('H:i:s'),
            // 'zbis' => $end->format('H:i:s')
        ];
        $query = $url . "&" . http_build_query($data);

        // get response and decode as json
        $response = file_get_contents($query);
        $json = json_decode($response, true);

        $importer = new Importer($json, $start, $end);

        // initialize the time vector with all possible rooms for every index
        $rooms = $importer->getRooms();
        $times = new TimeVector($importer->start, $importer->end, new DateInterval('PT15M'), $rooms);

        // get events and remove rooms at those times
        $indexTime = clone $importer->start;
        while ($indexTime < $importer->end) { // in case time span is more than one week
            $week = $start->format('Y') . '-W' . $start->format('W');

            foreach ($importer->getDays($week) as $day) {
                foreach ($importer->getEventInfos($day) as $eventInfo) {
                    $roomId = $eventInfo['veranstaltungsort'];

                    $eventId = $eventInfo['id'];
                    try {
                        $event = $importer->makeEvent($eventId);
                    } catch (Exception $e) {
                        continue;
                    }

                    $eventTime = clone $event->start;
                    while ($eventTime < $event->end) {
                        $times->remove($eventTime, $roomId);
                        $eventTime->add($times->offset);
                    }
                }
            }
            $indexTime->add(new DateInterval('P7D'));
        }
        return $times;
    }

    private function getRooms() : array
    {
        return $this->json['veranstaltungsorte'];
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
        if (count($eventJson) == 0) throw new Exception("event json empty"); // temporary
        
        $eventDate = $eventJson['datum'];
        $eventStart = $eventJson['beginn'];
        $eventEnd = $eventJson['ende'];

        $format = 'Y-m-d H:i:s';
        $eventStartDateTime = DateTime::createFromFormat($format, $eventDate . ' ' . $eventStart);
        $eventEndDateTime = DateTime::createFromFormat($format, $eventDate . ' ' . $eventEnd);

        return new Event($eventStartDateTime, $eventEndDateTime);
    }
}

// test
$start = new DateTime("today 08:00:00");
echo $start->format(DateTimeInterface::ISO8601) . "\n";
$end = new DateTime("today 10:00:00");
echo $end->format(DateTimeInterface::ISO8601) . "\n";

$rooms = Importer::query($start, $end);
