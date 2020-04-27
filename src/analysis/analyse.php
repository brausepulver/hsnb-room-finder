<?php declare(strict_types=1);

require_once(__DIR__ . '/../Import/Importer.php');

use Import\Importer;
use Import\Importer\{Event, Room, TimeVector};

class FreeRooms{

     public $id;
     public $building;
     public $number;
    //  public $start;
    //  public $end;
     public $free;

     public function __construct(){

        $this->free = [];
        
    }


}




//gets time interval
//puts every room id in an array
function getIDarray(Importer $importer){
    global $importer;
    $timevector = $importer->query();
    
    $times = $timevector->getAll();
    $timeID = [];
    for($i = 0; $i < count($times); $i++){
        $timeID[$i] = [];
        foreach ($times[$i] as $room){
            $timeID[$i][] = $room->id;

        }

    }
    return $timeID;
}



//calculate number based on minimal time
//used in analysis
 function minTimeLength(int $minTime) : int {
    return intval($minTime / 15);
}

//transforms 2d array to 1d array
//contains unique id2 values
function getUnique1D(array $input) : array {
    $output = array();
    for($i = 0; $i < count($input); $i++){
        for($j = 0; $j < count($input[$i]); $j++){
            $output[] = $input[$i][$j];
        }
    }
    $tmp = array_unique($output);                //maybe problem bc of array_unique
    $unique = [];
    foreach($tmp as $r){
        $unique[] = $r;
    }
    return $unique;
}

//checks if id from uniqueID is contained in timeID
//for every id => array of 0 and 1
//true => 1 (id in array)
//false => 0 (id not in array)
function convertID(array $timeID, array $uniqueID) : array {
    $convertedID[][] = array();
    for($i = 0; $i < count($uniqueID); $i++){
        $id = $uniqueID[$i];
        for($j = 0; $j < count($timeID); $j++){
            if(in_array($id, $timeID[$j]) == true){
                $convertedID[$i][$j] = 1;
            }else{
                $convertedID[$i][$j] = 0;
            }
        }

    }
    return $convertedID;
}

//checks availabilty based on minTime and the converted ID array (output convertID())
function checkTime(int $minTime, array $uniqueID, array $convertedID) : array {
    $mind = $minTime;
    $output = array();
    for($i = 0; $i < count($convertedID); $i++){
        $count = 0;
        $free = new FreeRooms();
        if(!in_array(0, $convertedID[$i])){
            $free->id = $uniqueID[$i];
            $free->free[0][0] = 0;
            $free->free[0][1] = count($convertedID[$i]);
            $output[$i] = $free;
        }else{
            $temp = 0;
            for($j = 0; $j < count($convertedID[$i]); $j++){
                if ($count >= $mind && ($convertedID[$i][$j] === 0 || $j === count($convertedID[$i])-1)) {
                    $free->id = $uniqueID[$i];
                    $free->free[$temp][0] = $j - $count;
                    $free->free[$temp][1] = $j;
                    $output[$i] = $free;
                    $temp++;
                    // $free = new FreeRooms();
                     $count = 0;
            } else if ($convertedID[$i][$j] === 1) {
                $count++;
                }
            }
        }
    }
    return $output;
}



//Test
//later values GUI interaction
$start = new DateTime("2020-W16-2 08:00:00");
$end = new DateTime("2020-W16-2 12:00:00");
$minTime = minTimeLength(30);

//overall problems may caused by empty room objects(veranstaltungsort missing)
$importer = new Importer($start, $end, true);

$timeID = getIDarray($importer);//check  
$uniqueID = getUnique1D($timeID);//check
$convertedID = convertID($timeID, $uniqueID);//check
$freerooms = checkTime($minTime, $uniqueID, $convertedID);
// foreach ($freerooms as $room) {
//     if ($room->start != 0) {
//         print_r($room);
//     }
// }
// print_r($freerooms);

// $out = print_r($freerooms, true);
// str_replace('\n', '<br>', $out);
// echo $out;



$name = 'L022017.2';
// L = Kategorie (hier Ort)
// 0 = 0
// 2 = Haus
// 1 = Gebäudeteil
// 304 = Raumnummer (erste Ziffer kann als Etage interpretiert werden)
// Es gibt auch "geteilte" Räume, wie Hörsaal 4 und 5 (L022017.2 und L022017.1).

updateFreeRoom($freerooms);
print_r($freerooms);


function getBuilding(string $name) : string{
    return substr($name, 2, 1);
}

function getRoomnumber(string $name) : string{
    return substr($name, 4);
}



// //Fehlerbehandlung falls Kurzname nicht vorhanden
function updateFreeRoom(array $freerooms){

    global $importer;
    $roomdata = $importer->getRooms();

    foreach($freerooms as $rooms){
        $id = $rooms->id;
        foreach($roomdata as $data){
            if($data->id == $id){
                $name = $data->shortName;                   
                $rooms->building = getBuilding($name);
                $rooms->number = getRoomnumber($name);
            }else{
                continue;
            }
        }
    }
}

