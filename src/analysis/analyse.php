<?php declare(strict_types=1);

require_once "../Import/Importer.php";
require_once "../Import/utility/Event.php";
require_once "../Import/utility/Room.php";
require_once "../Import/utility/TimeVector.php";

use Import\Importer;
use Import\Importer\{Event, Room, TimeVector};

// $load = spl_autoload("Importer.php");

$start = new DateTime("Monday next week 08:00:00");
$end = new DateTime("Monday next week 10:00:00");

$timevector = Importer::query($start, $end);

//eine methode -> analyse für einen tag
//schauen wie viele Tage -> methode so oft durchführen

// $minTime = 60 -> 4 mal

$times = $timevector->getAll();
print_r($times[0][4]->id2);

for($i = 0; $i < count($times); $i++){
    


}
