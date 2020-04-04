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

    public function __construct($json)
    {
        $this->id = $json['id'];
        $this->id2 = $json['id2'];
        $this->shortName = $json['kurzname'];
        $this->name = $json['name'];
    }

    public function __toString()
    {
        return $this->shortName;
    }
}
