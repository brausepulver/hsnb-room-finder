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

    public $free;

    public function __construct($json)
    {
        $this->id = $json['id'];
        $this->id2 = $json['id2'];
        $this->shortName = $json['kurzname'];
        $this->name = $json['name'];

        $this->building = substr($this->shortName, 2, 1);
        $this->number = substr($this->shortName, 4);
    }

    public function __toString()
    {
        return $this->shortName;
    }
}
