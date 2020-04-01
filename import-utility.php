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
    public $start;
    public $end;
    public $offset;

    private $times;

    /**
     * create a TimeVector using 
     * $default as the value for each index by copying it and 
     * $offset as the time difference between indices
     */
    public function __construct(DateTimeInterface $start, DateTimeInterface $end, DateInterval $offset, array $default)
    {
        $this->start = $start;
        $this->end = $end;
        $this->offset = $offset;
        $this->times = $this->buildTimeArray($offset, $default);
    }

    private function buildTimeArray(DateInterval $offset, array $default) : array
    {
        $times = [];
        $start = clone ($this->start);

        while ($start <= $this->end) {
            $times[] = $default;
            $start->add($offset);
        }
        return $times;
    }

    public function get(DateTimeInterface $indexTime)
    {
        $i = $this->timeToIndex($indexTime);
        return $this->timeArray[$i]; // error handling
    }

    public function remove(DateTimeInterface $indexTime, $elementId)
    {
        $i = $this->timeToIndex($indexTime);
        unset($this->times[$i][$elementId]); // error handling
    }

    private function timeToIndex(DateTimeInterface $indexTime) : int
    {
        $seconds = $indexTime->getTimestamp() - $this->start->getTimestamp();
        $minutes = $seconds / 60;
        $i = $minutes / 15; // array contains rooms in 15 minute steps
        return $i;
    }
}
