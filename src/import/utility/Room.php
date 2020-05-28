<?php declare(strict_types=1);
namespace Import\Utility;

class Room
{
    public $id;
    public $id2;
    public $shortName;
    public $name;

    public $type;
    public $size;
    public $number;
    public $building;

    private $occupiedTimeFrames;

    public function __construct($json)
    {
        $this->id = $json['id'];
        $this->id2 = $json['id2'];
        $this->shortName = $json['kurzname'];
        $this->name = $json['name'];

        $this->building = substr($this->shortName, 2, 1);
        $this->number = substr($this->shortName, 4);

        $this->occupiedTimeFrames = [];
    }

    public function __toString()
    {
        return $this->shortName;
    }

    public function occupy(Event $event)
    {
        $this->occupiedTimeFrames[] = [$event->start, $event->end];
    }

    public function getAvailableTimeFrames(\DateTimeInterface $start, \DateTimeInterface $finish)
    {
        $interval = new \DateInterval('PT15M');
        $v = new TimeVector($start, $finish, $interval, true);

        if (count($this->occupiedTimeFrames) === 0) {
            return [ [clone $start, clone $finish] ];
        }
        foreach ($this->occupiedTimeFrames as $timeFrame) {
            for ($i = clone $timeFrame[0]; $i < $timeFrame[1]; $i->add($interval)) {
                $v->set($i, false);
            }
        }
        $available = $v->toArray(); $availableOn = [];

        for ($i = 0, $j; $i < count($available); $i++) {
            $s = count($availableOn) > 0 ? count($availableOn[count($availableOn)-1]) : 0;

            if ($available[$i]) {
                if ($s === 2 || $s == 0) {
                    $availableOn[] = [$this->indexToTime($start, $i)];
                    $j = $this->indexToTime($start, $i);
                }
                $j->add($interval);
            }
            if ((!$available[$i] || $i === count($available)-1) && $s === 1) {
                $availableOn[count($availableOn)-1][1] = $j;
            }
        }
        return $availableOn;
    }

    /**
     * Make a time from an index given in the arrays contained in the free attribute of a FreeRooms object.
     * The indices are counted in 15 minute steps by default.
     * 
     * @param \DateTimeInterface $start, the timestamp at which the FreeRooms object begins counting.
     * @param int $index, representing one specific time.
     * @return \DateTimeInterface corresponding to the index added to the starting time.
     */
    private function indexToTime(\DateTimeInterface $start, int $index)
    {
        return (clone $start)->add(new \DateInterval('PT' . $index * 15 . 'M'));
    }
}
