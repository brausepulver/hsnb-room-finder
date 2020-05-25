<?php declare(strict_types=1);
namespace Import\Utility;

class Event
{
    public $start;
    public $end;

    public function __construct(array $eventJson)
    {
        if (count($eventJson) == 0) 
            throw new \InvalidArgumentException("Event JSON was empty. Could not construct Event object.");

        $eventDate = $eventJson['datum'];
        $eventStart = $eventJson['beginn'];
        $eventEnd = $eventJson['ende'];

        $format = 'Y-m-d H:i:s';
        $this->start = \DateTime::createFromFormat($format, $eventDate . ' ' . $eventStart);
        $this->end = \DateTime::createFromFormat($format, $eventDate . ' ' . $eventEnd);
    }
}
