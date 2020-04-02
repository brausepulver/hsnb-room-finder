<?php declare(strict_types=1);
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

    public function __construct($json)
    {
        $this->id = $json['id'];
        $this->shortName = $json['kurzname'];
        $this->name = $json['name'];
    }

    public function __toString()
    {
        return $this->shortName;
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
    public function __construct(\DateTimeInterface $start, \DateTimeInterface $end, \DateInterval $offset, array $default)
    {
        $this->start = $start;
        $this->end = $end;
        $this->offset = $offset;
        $this->times = $this->buildTimeArray($offset, $default);
    }

    private function buildTimeArray(\DateInterval $offset, array $default) : array
    {
        $times = [];
        $start = clone $this->start;

        while ($start <= $this->end) {
            $times[] = $default;
            $start->add($offset);
        }
        return $times;
    }

    public function get(\DateTimeInterface $indexTime)
    {
        $i = $this->timeToIndex($indexTime);
        return $this->timeArray[$i]; // error handling
    }

    public function remove(\DateTimeInterface $indexTime, $elementId)
    {
        $i = $this->timeToIndex($indexTime);
        unset($this->times[$i][$elementId]); // error handling
    }

    /**
     * converts a time to an index for the internal array based on the start time attribute
     * 
     * @param \DateTimeInterface $indexTime time to be converted
     * @throws \InvalidArgumentException if given time is before start time
     * @return int
     */
    private function timeToIndex(\DateTimeInterface $indexTime) : int
    {
        // get index time as a unix timestamp and offset from the start time
        $indexTimestamp = $indexTime->getTimestamp() - $this->start->getTimestamp();
        if ($indexTimestamp < 0) throw new \InvalidArgumentException('invalid index'); // internal array contains no negative indices

        // get offset interval as a unix timestamp
        $now = new DateTime('now');
        $nowPlusOffset = (clone $now)->add($this->offset);
        $offsetTimestamp = $nowPlusOffset->getTimestamp() - $now->getTimestamp();

        return $indexTimestamp / $offsetTimestamp;
    }
}
