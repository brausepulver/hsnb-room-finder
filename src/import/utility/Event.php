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
