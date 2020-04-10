<?php declare(strict_types=1);

require_once "../Import/Importer.php";
require_once "../Import/utility/Event.php";
require_once "../Import/utility/Room.php";
require_once "../Import/utility/TimeVector.php";

// use Import\Importer;
// use Import\Importer\{Event, Room, TimeVector};

require_once('../Import/Importer.php');
use Import\Importer;


class FreeRooms{

     private $id;
     private $start;
     private $end;

     private function __construct(int $id, int $start, int $end){

        $this->$id = $id;
        $this->start = $start;
        $this->end = $end; 
    }


}


//gets time interval
//puts every room id2 in an array
function getIDarray(DateTime $start, DateTime $end){
    $timevector = Importer::query($start, $end);
 
    $times = $timevector->getAll();
    $timeID;
    for($i = 0; $i < count($times); $i++){
        $times[$i];                         
        for($j = 0; $j < count($times[$i]); $j++){
            $timeID[$i][$j] = $times[$i][$j]->id2;
        }

    }
    echo 'done';
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
    $unique = array_unique($output);                //maybe problem bc of array_unique
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
    $mind = minTimeLength($minTime);
    $output = array();
    for($i = 0; $i < count($convertedID); $i++){
        $count = 0;
        $free = new FreeRooms();
        for($j = 0; $j < count($convertedID[$i]); $i++){
            if($convertedID[$i][$j] == 1){
                $count++;
            }else{
                if($count >= $mind){
                    $free->id = $uniqueID[$i]; //because convertedID and uniqueID have same structure
                    $free->$start = $j - $count;
                    $free->$end = $j - 1;
                }else{
                    $count = 0;
                    continue;
                }
            }
        }
        $output[$i] = $free;
    }
    return $output;
    echo 'done2';
}



//Test
//later values GUI interaction
$start = new DateTime("Wednesday next week 8:00:00");
$end = new DateTime("Wednesday next week 10:00:00");
$minTime = minTimeLength(60);

//overall problems may caused by empty room objects(veranstaltungsort missing)
$timeID = getIDarray($start, $end);//check  
$uniqueID = getUnique1D($timeID);//check
$convertedID = convertID($timeID, $uniqueID);//check
$freerooms = checkTime($minTime, $uniqueID, $convertedID);//problem
// print_r($freerooms);

