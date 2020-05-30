<?php declare(strict_types=1);
namespace Import\Utility;

class TimeVector
{
    public $start;
    public $finish;
    public $offset;

    private $array;

    /**
     * create a TimeVector using 
     * $default as the value for each index by copying it and 
     * $offset as the time difference between indices
     */
    public function __construct(\DateTimeInterface $start, \DateTimeInterface $finish, \DateInterval $offset, $default)
    {
        $this->start = $start;
        $this->finish = $finish;
        $this->offset = $offset;
        $this->array = $this->buildTimeArray($offset, $default);
    }

    private function buildTimeArray(\DateInterval $offset, $default) : array
    {
        $array = [];
        $start = clone $this->start;

        while ($start < $this->finish) {
            $array[] = $default;
            $start->add($offset);
        }
        return $array;
    }

    public function get(\DateTimeInterface $indexTime)
    {
        $i = $this->timeToIndex($indexTime);
        return $this->array[$i];
    }

    public function set(\DateTimeInterface $indexTime, $value)
    {
        $i = $this->timeToIndex($indexTime);
        $this->array[$i] = $value;
    }

    public function remove(\DateTimeInterface $indexTime, $elementId)
    {
        try {
            $i = $this->timeToIndex($indexTime);
        } catch (\InvalidArgumentException $e) {
            return;
        }
        unset($this->array[$i][$elementId]);
    }

    /**
     * converts a time to an index for the internal array based on the start time attribute
     * 
     * @param \DateTimeInterface $indexTime time to be converted
     * @return int
     */
    private function timeToIndex(\DateTimeInterface $indexTime) : int
    {
        if ($indexTime < $this->start) {
            $indexTime = clone $this->start;
        } else if ($indexTime > $this->finish) {
            $indexTime = clone $this->finish;
        }
        // get index time as a unix timestamp and offset from the start time
        $indexTimestamp = $indexTime->getTimestamp() - $this->start->getTimestamp();

        // get offset interval as a unix timestamp
        $now = new \DateTime('now');
        $nowPlusOffset = (clone $now)->add($this->offset);
        $offsetTimestamp = $nowPlusOffset->getTimestamp() - $now->getTimestamp();

        return $indexTimestamp / $offsetTimestamp;
    }

    public function toArray() : array
    {
        return $this->array;
    }
}
