<?php declare(strict_types=1);
namespace Import\Utility;

class Room
{
    public $id;
    public $shortName;
    public $name;

    public $type;
    public $size;
    public $number;
    public $building;

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
