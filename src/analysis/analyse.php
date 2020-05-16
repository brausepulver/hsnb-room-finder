<?php declare(strict_types=1);

require_once(__DIR__ . '/../import/Importer.php');

use Import\Importer;
use Import\Importer\{Event, Room, TimeVector};

class FreeRooms{

     public $id;
     public $building;
     public $number;
     //public $info;
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
    updateFreeRoom($output);
    return $output;
}



//Test
//later values GUI interaction
// $start = new DateTime("2020-W16-2 08:00:00");
// $end = new DateTime("2020-W16-2 10:00:00");
// $minTime = minTimeLength(60);

// $importer = new Importer($start, $end, true);

// $timeID = getIDarray($importer);//check  
// $uniqueID = getUnique1D($timeID);//check
// $convertedID = convertID($timeID, $uniqueID);//check
// $freerooms = checkTime($minTime, $uniqueID, $convertedID);
// // foreach ($freerooms as $room) {
// //     if ($room->start != 0) {
// //         print_r($room);
// //     }
// // }
// print_r($freerooms);

// $out = print_r($freerooms, true);
// str_replace('\n', '<br>', $out);
// echo $out;



// $name = 'L022017.2';
// // L = Kategorie (hier Ort)
// // 0 = 0
// // 2 = Haus
// // 1 = Gebäudeteil
// // 304 = Raumnummer (erste Ziffer kann als Etage interpretiert werden)
// // Es gibt auch "geteilte" Räume, wie Hörsaal 4 und 5 (L022017.2 und L022017.1).

// $conditions = [];
// $conditions['building'] = null;
// $conditions['number'] = null;
// $freerooms = getFreeRooms($start, $end, true, $conditions);
// print_r($freerooms);


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
                if(!empty($name)){                   
                    $rooms->building = getBuilding($name);
                    $rooms->number = getRoomnumber($name);
                    $rooms->info = $data->name;
                }else{
                    //id merken und in Liste packen
                    continue;
                }
            }else{
                continue;
            }
        }
    }
}


//hier Bearbeitung

function getFreeRooms($start, $end, $debug = false, $conditions)
{
    global $importer;
    $importer = new Importer($start, $end, $debug);
    $output = [];

    // $minTime = minTimeLength(intval($minTimeIn));
    $minTime = minTimeLength(15);
    $timeID = getIDarray($importer);
    $uniqueID = getUnique1D($timeID);
    $convertedID = convertID($timeID, $uniqueID);
    $freerooms = checkTime($minTime, $uniqueID, $convertedID);
    //check conditions
    $building = $conditions['building'];
    $number = $conditions['number'];
    // $type = $conditions['type'];

    if(empty($building) && empty($number)){     //later add && empty($type)
        return $freerooms;
    }else{
        if(!empty($building)){
            $output = getRoomsbyBuilding($freerooms, $building);   
        }
         if(!empty($number)){
             $output = getRoomsbyNumber($freerooms, $number);
        }
        return $output;
    }
    // if(!$type = null){
    //     $output = getRoomsbyType($freerooms, $type);
    // }

}

function getRoomsbyNumber(array $freerooms, string $numberin) : array {
    $output = [];
    $number = floatval($numberin);
    foreach($freerooms as $room){
        $roomnumber = floatval($room->number);
        if($roomnumber == $number){
            $output[] = $room;
        }else{
            continue;
        }
    }
    return $output;
}

function getRoomsbyBuilding(array $freerooms, string $buildingin) : array {
    $output = [];
    $building = intval($buildingin);
    foreach($freerooms as $room){
        $roombuilding = intval($room->building);
        if($roombuilding == $building){
            $output[] = $room;
        }else{
            continue;
        }
    }
    return $output;
}

function getRoomsbyType(array $freerooms, string $typein) : array{
    //analyse roomtype


    
}

$importer;
